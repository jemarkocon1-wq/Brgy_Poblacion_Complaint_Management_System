<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'municipality', 'province', 'address', 'phone', 'email', 'office_hours'])]
class BarangayInformation extends Model
{
}
