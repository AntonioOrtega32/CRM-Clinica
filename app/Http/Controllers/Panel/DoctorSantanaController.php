<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\Trayectoria;
use App\Models\Galeria;
use App\Models\Blog;
use App\Models\blogdr;
use App\Models\Contacto;
use App\Models\certificaciones;
use App\Models\Movimiento; // <-- Importa tu modelo de movimientos
use App\Models\resultadosdr;
use Illuminate\Support\Facades\DB;

class DoctorSantanaController extends Controller
{

    //Funcion de registro de movimientos
    protected function registraMovimiento($tipo, $descripcion, $tabla, $registro_id = null)
    {
        Movimiento::create([
            'usuario_id' => Auth::id(),
            'tipo_movimiento' => $tipo,
            'descripcion' => $descripcion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registro_id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function indexDrsantana()
    {
        //$trayectorias = Trayectoria::latest()->paginate(10);
        $galerias = Galeria::latest()->paginate(12);
        $blogs = blogdr::all();
        //$contactos = Contacto::latest()->paginate(10);
        $resultados = resultadosdr::all();

        $certificaciones = certificaciones::all();
        //$contactos = Contacto::latest()->paginate(10);
        //dd($certificaciones);
        return view('panel.landing.drsantana.index', compact('blogs', 'galerias', 'certificaciones', 'resultados'));
    }

    public function indexsBlog()
    {
        $blogs = blogdr::all();

        return view('panel.landing.drsantana.blogIndex', compact('blogs'));
    }

     public function storeBlogdr(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'fecha' => 'required|date',
            'imagen' => 'nullable',
        ]);

        $data = $request->only(['titulo', 'contenido', 'fecha', 'link']);

        // Procesar imagen si se subió
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/blog'), $nombreArchivo);
            $data['imagen'] = 'images/blog/' . $nombreArchivo;
        }

        $blogdr = blogdr::create($data);

        $this->registraMovimiento(
            'Crear',
            "Se creó blog Dr.: {$blogdr->titulo}",
            'blogdrs',
            $blogdr->id
        );

          return redirect()->back()->with('success', 'Blog Dr. actualizado correctamente.');
    }

  public function updateBlogdr(Request $request, $id)
{
    $blog = DB::table('blogdrs')->where('id', $id)->first();

    if (!$blog) {
        return redirect()->back()->with('error', 'Registro no encontrado.');
    }

    $data = [
        'titulo'     => $request->input('titulo', $blog->titulo),
        'contenido'  => $request->input('contenido', $blog->contenido),
        'fecha'      => $request->input('fecha', $blog->fecha),
         'link'       => $request->input('link', $blog->link),
        'updated_at' => now(),
    ];

    if ($request->hasFile('imagen')) {
        // Eliminar imagen anterior si existe
        if ($blog->imagen && file_exists(public_path($blog->imagen))) {
            unlink(public_path($blog->imagen));
        }

        $file = $request->file('imagen');
        $nombreArchivo = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('images/blog'), $nombreArchivo);

        $data['imagen'] = 'images/blog/' . $nombreArchivo;
    }

    DB::table('blogdrs')->where('id', $id)->update($data);

    $this->registraMovimiento(
        'Actualizar',
        "Se actualizó blog Dr.: {$data['titulo']}",
        'blogdrs',
        $id
    );

    return redirect()->back()->with('success', 'Blog Dr. actualizado correctamente.');
}

public function destroyBlogdr($id)
{
    $blog = blogdr::find($id);

    if (!$blog) {
        return redirect()->back()->with('error', 'Entrada no encontrada.');
    }

    // Eliminar imagen si existe
    if ($blog->imagen && file_exists(public_path($blog->imagen))) {
        unlink(public_path($blog->imagen));
    }

    $blog->delete();

    $this->registraMovimiento(
        'Eliminar',
        "Se eliminó blog Dr.: {$blog->titulo}",
        'blogdrs',
        $id
    );

    return redirect()->back()->with('success', 'Entrada eliminada correctamente.');
}


    // Función para obtener todos los blogs
    public function getBlogsdr()
    {
        try {
            $blogs = blogdr::orderBy('fecha', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $blogs,
                'count' => $blogs->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar artículos: ' . $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // GALERÍA
    // -----------------------------
    public function indexGaleria()
    {
        $galerias = Galeria::all();
        return view('panel.landing.drsantana.galeriaIndex', compact('galerias'));
    }

    public function storeGaleria(Request $request)
    {
        $request->validate([
            'imagen' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:1000000',
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'nullable|in:imagen,video'
        ]);

        $galeriaData = $request->only(['titulo', 'descripcion', 'tipo']);

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();

            // Mover a carpeta correspondiente
            $carpeta = $request->tipo == 'video' ? 'videos/galeria' : 'images/galeria';
            $file->move(public_path($carpeta), $nombreArchivo);

            $galeriaData['imagen'] = $carpeta . '/' . $nombreArchivo;
        }

        $galeria = Galeria::create($galeriaData);

        $this->registrarMovimiento('CREAR',
         "Se agregó {$request->tipo} a la galería: {$galeria->titulo}",
         'galerias', $galeria->id);


        return redirect()->back()->with('success', 'Imagen o video agregado a la galería correctamente.');
    }

    public function updateGaleria(Request $request, Galeria $galeria)
    {
        if ($request->hasFile('imagen')) {
            // Eliminar archivo anterior si existe
            if ($galeria->imagen && file_exists(public_path($galeria->imagen))) {
                unlink(public_path($galeria->imagen));
            }

            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();

            // Determinar carpeta basado en tipo o extensión
            $tipo = $request->tipo ?? (in_array($file->getClientOriginalExtension(), ['mp4', 'mov', 'avi']) ? 'video' : 'imagen');
            $carpeta = $tipo == 'video' ? 'videos/galeria' : 'images/galeria';
            $file->move(public_path($carpeta), $nombreArchivo);

            $galeria->imagen = $carpeta . '/' . $nombreArchivo;
            $galeria->tipo = $tipo;
        }

        $galeria->titulo = $request->titulo ?? $galeria->titulo;
        $galeria->descripcion = $request->descripcion ?? $galeria->descripcion;
        $galeria->save();

        $this->registrarMovimiento('ACTUALIZAR',
        "Se actualizó elemento de la galería: {$galeria->titulo}",
        'galerias', $galeria->id);

        return redirect()->back()->with('success', 'Galería actualizada correctamente.');
    }

    public function destroyGaleria(Galeria $galeria)
    {
        // Eliminar archivo físico si existe
        if ($galeria->imagen && file_exists(public_path($galeria->imagen))) {
            unlink(public_path($galeria->imagen));
        }

        $this->registrarMovimiento('ELIMINAR', "Se eliminó elemento de galería: {$galeria->titulo}", 'galerias', $galeria->id);

        $galeria->delete();

        return redirect()->back()->with('success', 'Elemento de la galería eliminado correctamente.');
    }


    //------------------------------------------
    // CERTIFICACIONES
    //------------------------------------------

    public function indexCertificaciones()
    {
        $certificaciones = certificaciones::all();
        return view('panel.landing.drsantana.certificacionesIndex', compact('certificaciones'));
    }

    public function CerStore(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $certificacion = new certificaciones();
        $certificacion->titulo = $request->titulo;
        $certificacion->descripcion = $request->descripcion;

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/certificaciones'), $nombreArchivo);
            $certificacion->imagen = 'images/certificaciones/' . $nombreArchivo;
        }

        $certificacion->save();

        return redirect()->back()->with('success', 'Certificación creada con éxito');
    }

    /**
     * Actualizar certificación
     */
    public function CerUpdate(Request $request, $id)
    {

        $certificacion = certificaciones::findOrFail($id);
        $certificacion->titulo = $request->titulo;
        $certificacion->descripcion = $request->descripcion;

        if ($request->hasFile('imagenc')) {
            // Borrar la imagen anterior si existe
            if ($certificacion->imagen && file_exists(public_path($certificacion->imagen))) {
                unlink(public_path($certificacion->imagen));
            }

            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/certificaciones'), $nombreArchivo);
            $certificacion->imagen = 'images/certificaciones/' . $nombreArchivo;
        }

        $certificacion->save();

        $this->registraMovimiento(
            'Actualizar',
            "Se actualizó la certificación: {$certificacion->titulo}",
            'blogdrs',
            $certificacion->id
        );

        return redirect()->back()->with('success', 'Certificación actualizada con éxito');
    }

    /**
     * Eliminar certificación
     */
    public function CerDestroy($id)
    {
        $certificacion = certificaciones::findOrFail($id);

        // Borrar imagen asociada si existe
        if ($certificacion->imagen && file_exists(public_path($certificacion->imagen))) {
            unlink(public_path($certificacion->imagen));
        }

        $certificacion->delete();

        return redirect()->back()->with('success', 'Certificación eliminada con éxito');
    }

    // Resultados Dr. Santana
    public function crceaResultado()
    {
        $resultados = resultadosdr::all();
        return view('panel.landing.drsantana.claculadoraDR', compact('resultados'));
    }

    public function storeResultadoDR(Request $request)
{
    // Verificar si ya existen 2 registros
    $total = resultadosdr::count();
    if ($total >= 2) {
        return redirect()
            ->back()
            ->with('error', 'Solo se permiten dos resultados. Elimine uno existente antes de agregar otro.')
            ->withInput();
    }

    // Validación
    $request->validate([
        'titulo' => 'required|string',
        'color' => 'required|string',
        'numero' => 'required|numeric',
        'icono_svg' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
    ]);

    // Crear el registro
    $resultado = new resultadosdr();
    $resultado->titulo = $request->titulo;
    $resultado->color = $request->color;
    $resultado->numero = $request->numero;

    if ($request->hasFile('icono_svg')) {
        $file = $request->file('icono_svg');
        $nombreArchivo = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('images/resultadosDR'), $nombreArchivo);
        $resultado->icono_svg = 'images/resultadosDR/' . $nombreArchivo;
    }

    $resultado->save();

    $this->registrarMovimiento(
        'Crear',
        "Se creó resultado: {$resultado['titulo']}",
        'resultadosdr',
        $resultado->id
    );

    return redirect()->route('panel.drsantana.index')->with('success', 'Resultado creado correctamente');
}


    public function updateResultadoDR(Request $request, resultadosdr $resultadosdr)
{

    $resultadosdr->titulo = $request->titulo;
    $resultadosdr->numero = $request->numero;
    $resultadosdr->color = $request->color;
    //$resultado->updated_by = auth()->id();

    if ($request->hasFile('icono_svg')) {
        $file = $request->file('icono_svg');
        $nombreArchivo = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('images/resultados'), $nombreArchivo);
        $resultadosdr->icono_svg = 'images/resultados/'.$nombreArchivo;
    }

    $resultadosdr->save();

    Movimiento::create([
        //'usuario_id' => auth()->id(),
        'tipo_movimiento' => 'Actualizar',
        'descripcion' => 'Se actualizó el resultado: '.$resultadosdr->titulo,
        'tabla_afectada' => 'resultados',
        'registro_id' => $resultadosdr->id,
    ]);

    return redirect()->route('panel.drsantana.index')->with('success', 'Resultado actualizado correctamente');
}

    //Todoo esto no funciona todavia xdxdxdcx
    // -----------------------------
    // CONTACTO
    // -----------------------------
    public function indexContacto()
    {
        $contactos = Contacto::latest()->paginate(10);
        return view('panel.drsantana.contacto.index', compact('contactos'));
    }

    public function showContacto(Contacto $contacto)
    {
        return view('panel.drsantana.contacto.show', compact('contacto'));
    }

    public function destroyContacto(Contacto $contacto)
    {
        $this->registrarMovimiento('ELIMINAR', "Se eliminó mensaje de contacto: {$contacto->nombre}", 'contactos', $contacto->id);

        $contacto->delete();
        return back()->with('success', 'Mensaje eliminado.');
    }

    // -----------------------------
    // REGISTRO DE MOVIMIENTOS
    // -----------------------------
    protected function registrarMovimiento($tipo, $descripcion, $tabla, $registro_id = null)
    {
        Movimiento::create([
            'usuario_id' => Auth::id(),
            'tipo_movimiento' => $tipo,
            'descripcion' => $descripcion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registro_id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }


    public function indexTrayectoria()
    {
        $trayectorias = Trayectoria::latest()->paginate(10);
        return view('panel.landing.drsantana.trayectoria', compact('trayectorias'));
    }

    public function storeTrayectoria(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
        ]);

        $trayectoria = Trayectoria::create($request->all());

        $this->registrarMovimiento('Crear', "Se creó trayectoria: {$trayectoria->titulo}", 'trayectorias', $trayectoria->id);

        return back()->with('success', 'Trayectoria creada correctamente.');
    }

    public function updateTrayectoria(Request $request, Trayectoria $trayectoria)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
        ]);

        $trayectoria->update($request->all());

        $this->registrarMovimiento('ACTUALIZAR', "Se actualizó trayectoria: {$trayectoria->titulo}", 'trayectorias', $trayectoria->id);

        return back()->with('success', 'Trayectoria actualizada correctamente.');
    }

    public function destroyTrayectoria(Trayectoria $trayectoria)
    {
        $this->registrarMovimiento('ELIMINAR', "Se eliminó trayectoria: {$trayectoria->titulo}", 'trayectorias', $trayectoria->id);

        $trayectoria->delete();
        return back()->with('success', 'Trayectoria eliminada.');
    }
}
