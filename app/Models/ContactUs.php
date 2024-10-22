<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model{

	protected $table = 'contactus';

	protected $fillable = [
		'first',
		'last',
		'number',
		'message',
		'venue',
		'email',
		'services',
		'created_at',
	];
}
?>