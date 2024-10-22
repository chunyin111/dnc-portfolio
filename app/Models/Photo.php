<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model{

	protected $table = 'photo';

	protected $fillable = [
		'pic_name',
		'put_at',
		'size',
		'category',
		'sort',
		'status',
	];
}
?>