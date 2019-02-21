<?php

namespace Davidcb\Uploads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class File extends Model implements Sortable
{
	use SortableTrait;

	public $sortable = [
		'order_column_name' => 'orderby',
		'sort_when_creating' => true,
	];

	protected $fillable = ['title', 'folder', 'subfolders', 'url', 'orderby', 'fileable_id', 'fileable_type'];

	public function fileable()
	{
		return $this->morphTo();
	}

	public function getSizeAttribute()
	{
		return round(Storage::size($this->folder . '/' . $this->url) / 1024 / 1024, 2);
	}
}
