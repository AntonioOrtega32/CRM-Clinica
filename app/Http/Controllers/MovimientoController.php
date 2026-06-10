<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Illuminate\Http\Request;

class MovimientoController extends Controller
{
    public function index() {
        $movimientos = Movimiento::latest()->paginate(20);
        return view('admin.movimientos.index', compact('movimientos'));
    }
}
