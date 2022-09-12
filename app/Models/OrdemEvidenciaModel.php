<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdemEvidenciaModel extends Model
{
	protected $table                = 'ordens_evidencias';
	protected $returnType           = 'object';
	protected $allowedFields        = [
		'ordem_id',
		'evidencia',
	];
}
