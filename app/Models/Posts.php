<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model{

	protected $table = 'posts';

	protected $fillable = [
		'title',
		'description',
		'first_image',
		'image',
		'category',
		'status',
		'created_at',
	];
}
?>