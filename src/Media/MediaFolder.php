<?php
namespace MadisonSolutions\LCF\Media;

use DB;
use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    // Define database table
    protected $table = 'lcf_media_folders';

    protected $fillable = ['parent_id', 'name', 'description'];

    protected $casts = [
        'parent_id' => 'integer',
    ];

    protected static $top;

    public static function tree(bool $rebuild = false)
    {
        if ($rebuild || is_null(MediaFolder::$top)) {
            $index = [];
            MediaFolder::$top = [];

            $rows = DB::table('lcf_media_folders')
                ->select(['id', 'parent_id as parent', 'name', 'description'])
                ->orderBy('name', 'asc')
                ->get();

            foreach ($rows as $row) {
                $index[$row->id] = $row;
                $row->children = [];
            }

            foreach ($index as $row) {
                $row->parent = ($index[$row->parent] ?? null);
                if ($row->parent) {
                    $row->parent->children[] = $row;
                } else {
                    MediaFolder::$top[] = $row;
                }
            }
        }

        return MediaFolder::$top;
    }

    public static function findByPath(string $path)
    {
        $find_named = function ($rows, $name) {
            if (empty($name) || empty($rows)) {
                return null;
            }
            foreach ($rows as $row) {
                if ($row->name == $name) {
                    return $row;
                }
            }
            return null;
        };

        $path = explode('/', $path);
        $row = $find_named(self::tree(), array_shift($path));
        while ($row && $path) {
            $row = $find_named($row->children, array_shift($path));
        }
        return $row;
    }

    public function items()
    {
        return $this->hasMany(MediaItem::class, 'folder_id');
    }
}
