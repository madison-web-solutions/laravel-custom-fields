<?php

namespace MadisonSolutions\LCF\Media;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\LCF;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {
        $this->authorize('index', MediaItem::class);
        $request->validate([
            'search' => 'nullable|string',
            'category' => 'nullable|string',
            'folder_id' => 'nullable|integer',
        ]);
        $query = MediaItem::query()->orderBy('updated_at', 'desc');
        $ilike = LCF::iLikeOperator($query->getConnection());

        $search = $request->input('search');
        if ($search) {
            $words = preg_split('/\s+/', $search);
            foreach ($words as $word) {
                $query->where(function ($q) use ($ilike, $word) {
                    $wordLike = '%' . str_replace('%', '\\%', $word) . '%';
                    $q->where('title', $ilike, $wordLike)->orWhere('alt', $ilike, $wordLike)->orWhere('extension', $word);
                });
            }
        }
        $category = $request->input('category');
        if ($category) {
            $query->whereIn('extension', MediaType::allExtensionsForCategory($category));
        }
        $folder_id = $request->input('folder_id');
        if ($folder_id) {
            $query->where('folder_id', $folder_id);
        }
        $items = $query->paginate(50);
        return MediaItemResource::collection($items);
    }

    public function get(Request $request, $id)
    {
        $item = MediaItem::findOrFail($id);
        $this->authorize('get', $item);
        return [
            'ok' => true,
            'item' => new MediaItemResource($item),
        ];
    }

    public function upload(Request $request)
    {
        $this->authorize('upload', MediaItem::class);
        if (!$request->hasFile('file')) {
            return ['ok' => false, 'error' => 'No file attached'];
        }
        $file = $request->file('file');
        if (!$file->isValid()) {
            return ['ok' => false, 'error' => 'Upload not valid'];
        }
        $extension = $file->getClientOriginalExtension();
        $mediaType = new MediaType($extension);
        if ($mediaType->category == 'Unknown') {
            return ['ok' => false, 'error' => 'Unrecognised format'];
        }
        $category = $request->input('category', null);
        if ($category && strtolower($mediaType->category) !== $category) {
            return ['ok' => false, 'error' => "Must upload a file of type: {$category}, received type {$mediaType->category}"];
        }
        if (Coerce::toInt($request->input('folder_id', null), $folder_id)) {
            $folder = MediaFolder::find($folder_id);
        } else {
            $folder = null;
        }
        $suffixLen = strlen($extension) + 1;
        $basename = substr($file->getClientOriginalName(), 0, -$suffixLen);
        $item = new MediaItem([
            'title' => substr($basename, 0, 128),
            'extension' => $mediaType->extension,
            'alt' => '',
            'folder_id' => ($folder ? $folder->id : null),
        ]);
        $item->setUniqueSlug($basename);
        $item->getStorageItem()->setFileFromUpload($file);
        $item->save();
        return [
            'ok' => true,
            'item' => new MediaItemResource($item),
        ];
    }

    public function update(Request $request, $id)
    {
        $item = MediaItem::findOrFail($id);
        $this->authorize('update', $item);
        $request->validate([
            'title' => 'required|string|max:128',
            'alt' => 'nullable|string|max:256',
            'folder_id' => 'nullable|integer|exists:lcf_media_folders,id',
            'replace_file' => 'nullable|file'
        ]);

        if ($request->hasFile('replace_file')) {
            $file = $request->file('replace_file');
            if (! $file->isValid()) {
                throw ValidationException::withMessages(['replace_file' => ["File upload invalid"]]);
            }
            $extension = $file->getClientOriginalExtension();
            $mediaType = new MediaType($extension);
            if ($mediaType->category == 'Unknown') {
                throw ValidationException::withMessages(['replace_file' => ["Unrecognised file format"]]);
            }
            if ($mediaType->category != $item->type->category) {
                throw ValidationException::withMessages(['replace_file' => ["Incompatible file - cannot replace a {$item->type->category} with a {$mediaType->category}"]]);
            }
            $item->extension = $mediaType->extension;
            $item->getStorageItem()->setFileFromUpload($file);
        }

        $item->title = $request->title;
        $item->alt = $request->alt ?? '';
        $item->folder_id = $request->folder_id;
        $item->save();

        return [
            'ok' => true,
            'item' => new MediaItemResource($item),
        ];
    }

    public function delete(Request $request, $id)
    {
        $item = MediaItem::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();
        return [
            'ok' => true,
        ];
    }

    protected function folderPath($node)
    {
        $path = [$node->name];
        $curr = $node;
        while ($curr = $curr->parent) {
            $path[] = $curr->name;
        }
        return array_reverse($path);
    }

    protected function folderData($rebuild = false)
    {
        $folder_data = [];
        $recurse = function ($nodes) use (&$folder_data, &$recurse) {
            foreach ($nodes as $node) {
                $folder_data[] = [
                    'id' => $node->id,
                    'name' => $node->name,
                    'path' => $this->folderPath($node),
                    'parent_id' => ($node->parent ? $node->parent->id : null),
                    'description' => $node->description,
                ];
                $recurse($node->children);
            }
        };
        $recurse(MediaFolder::tree($rebuild));
        return $folder_data;
    }

    protected function folderChoices(?MediaFolder $exclude = null)
    {
        $choices = [];
        $recurse = function ($nodes) use ($exclude, &$choices, &$recurse) {
            foreach ($nodes as $node) {
                if ($exclude && $node->id == $exclude->id) {
                    // Prevent circular references by skipping the specified folder (and all its children)
                    continue;
                }
                $choices[$node->id] = implode('/', $this->folderPath($node));
                $recurse($node->children);
            }
        };
        $recurse(MediaFolder::tree());
        return $choices;
    }

    public function folders(Request $request)
    {
        $this->authorize('index', MediaItem::class);
        return [
            'folders' => $this->folderData(),
        ];
    }

    protected function editFolderFields(MediaFolder $folder)
    {
        return [
            'name' => LCF::newTextField([
                'label' => 'Folder Name',
                'required' => true,
                'max' => 64,
            ]),
            'description' => LCF::newTextAreaField([
                'label' => 'Description',
            ]),
            'parent_id' => LCF::newChoiceField([
                'label' => 'Parent Folder',
                'choices' => $this->folderChoices($folder),
                'default' => $folder->parent_id,
                'placeholder' => 'None (top-level folder)',
            ]),
        ];
    }

    protected function deleteFolderFields(MediaFolder $folder)
    {
        return [
            'reassign_id' => LCF::newChoiceField([
                'label' => 'Reassign To',
                'choices' => $this->folderChoices($folder),
                'placeholder' => 'None (top-level folder)',
                'help' => 'Where should the contents of this folder be moved to?',
            ]),
        ];
    }

    public function createFolder(Request $request)
    {
        $this->authorize('manageFolders', MediaItem::class);
        $folder = new MediaFolder();
        return $this->createOrAddFolder($request, $folder);
    }

    public function editFolder(Request $request, $id)
    {
        $this->authorize('manageFolders', MediaItem::class);
        $folder = MediaFolder::findOrFail($id);
        return $this->createOrAddFolder($request, $folder);
    }

    protected function createOrAddFolder(Request $request, MediaFolder $folder)
    {
        $edit_fields = $this->editFolderFields($folder);

        if ($request->isMethod('post')) {
            $request->lcfCoerce($edit_fields);
            $request->lcfValidate($edit_fields, []);
            $folder->fill($request->only(['name', 'description', 'parent_id']))->save();

            // Return the updated list of folder data
            return [
                'folders' => $this->folderData(true),
            ];
        }

        return [
            'folder' => $folder,
            'edit_fields' => $edit_fields,
            'delete_fields' => ($folder ? $this->deleteFolderFields($folder) : null),
        ];
    }

    public function deleteFolder(Request $request, $id)
    {
        $this->authorize('manageFolders', MediaItem::class);
        $folder = MediaFolder::findOrFail($id);
        $delete_fields = $this->deleteFolderFields($folder);
        $request->lcfCoerce($delete_fields);
        $request->lcfValidate($delete_fields, []);

        // Reassign child folders and media items
        MediaItem::where('folder_id', $id)->update(['folder_id' => $request->reassign_id]);
        MediaFolder::where('parent_id', $id)->update(['parent_id' => $request->reassign_id]);

        // Delete the folder
        $folder->delete();

        // Return the updated list of folder data
        return [
            'folders' => $this->folderData(true),
        ];
    }
}
