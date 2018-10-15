<?php
namespace MadisonSolutions\LCF\Media;

class MediaType
{
    protected $extension;
    protected $label;
    protected $mimeType;
    protected $category;
    protected $sizable;

    public function __construct(string $extension)
    {
        $extension = strtolower(ltrim($extension, '.'));
        $alternatives = [
            'jpeg' => 'jpg',
            'tiff' => 'tif',
        ];
        $extension = $alternatives[$extension] ?? $extension;
        $this->extension = $extension;

        switch ($extension) {
            case 'jpg':
                $this->label = 'JPG Image';
                $this->mimeType = 'image/jpeg';
                $this->category = 'Image';
                $this->sizable = true;
                break;
            case 'png':
                $this->label = 'PNG Image';
                $this->mimeType = 'image/png';
                $this->category = 'Image';
                $this->sizable = true;
                break;
            case 'gif':
                $this->label = 'GIF Image';
                $this->mimeType = 'image/gif';
                $this->category = 'Image';
                $this->sizable = true;
                break;
            case 'tif':
                $this->label = 'TIFF Image';
                $this->mimeType = 'image/tiff';
                $this->category = 'Image';
                $this->sizable = true;
                break;
            case 'svg':
                $this->label = 'SVG Image';
                $this->mimeType = 'image/svg+xml';
                $this->category = 'Image';
                $this->sizable = false;
                break;
            case 'txt':
                $this->label = 'Plain Text File';
                $this->mimeType = 'text/plain';
                $this->category = 'Document';
                $this->sizable = false;
                break;
            case 'rtf':
                $this->label = 'Rich Text File';
                $this->mimeType = 'application/rtf';
                $this->category = 'Document';
                $this->sizable = false;
                break;
            case 'md':
                $this->label = 'Markdown Document';
                $this->mimeType = 'text/markdown';
                $this->category = 'Document';
                $this->sizable = false;
                break;
            case 'pdf':
                $this->label = 'PDF Document';
                $this->mimeType = 'application/pdf';
                $this->category = 'Document';
                $this->sizable = false;
                break;
            case 'doc':
                $this->label = 'MS Word Document';
                $this->mimeType = 'application/msword';
                $this->category = 'Document';
                $this->sizable = false;
                break;
            case 'docx':
                $this->label = 'MS Word Document';
                $this->mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                $this->category = 'Document';
                $this->sizable = false;
                break;
            case 'xls':
                $this->label = 'MS Excel Spreadsheet';
                $this->mimeType = 'application/vnd.ms-excel';
                $this->category = 'Spreadsheet';
                $this->sizable = false;
                break;
            case 'xlsx':
                $this->label = 'MS Excel Spreadsheet';
                $this->mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                $this->category = 'Spreadsheet';
                $this->sizable = false;
                break;
            case 'csv':
                $this->label = 'CSV File';
                $this->mimeType = 'text/csv';
                $this->category = 'Spreadsheet';
                $this->sizable = false;
                break;
            case 'ppt':
                $this->label = 'Powerpoint Presentation';
                $this->mimeType = 'application/vnd.ms-powerpoint';
                $this->category = 'Presentation';
                $this->sizable = false;
                break;
            case 'pptx':
                $this->label = 'Powerpoint Presentation';
                $this->mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                $this->category = 'Presentation';
                $this->sizable = false;
                break;
            case 'unknown':
            default:
                $this->label = 'Unknown Type';
                $this->mimeType = '';
                $this->category = 'Unknown';
                $this->sizable = false;
                break;
        }
    }

    public function __get($key)
    {
        switch ($key) {
            case 'extension':
            case 'label':
            case 'mimeType':
            case 'category':
            case 'sizable':
                return $this->$key;
        }
    }

    public function __isset($key)
    {
        switch ($key) {
            case 'extension':
            case 'label':
            case 'mimeType':
            case 'category':
            case 'sizable':
                return true;
        }
        return false;
    }

    public static function allCategories()
    {
        $categories = [];
        foreach (static::members() as $type) {
            $categories[$type->category] = true;
        }
        return array_keys($categories);
    }
}
