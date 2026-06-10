<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Resultado;
use App\Models\Movimiento;
use App\Models\QuienesSomos;
use App\Models\encabezado;
use App\Models\Blog;
use App\Models\servicios;
use App\Models\CasoExito;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class LandingController extends Controller
{
    public function index()
    {
        $resultados = Resultado::all();        $resultados = Resultado::all();
        $quienes_somos = QuienesSomos::all(); // Traemos info de 'Quiénes Somos'
        $encabezados = encabezado::all(); // Traemos info de 'Encabezado'
        $blogs = Blog::all(); // Traemos info de 'Blog'
        $servicios = servicios::all(); // Traemos info de 'Servicios' 
        $casos = CasoExito::all(); // Traemos info de 'Casos de Éxito'

        return view('panel.landing.index',
        compact('resultados', 
        'encabezados', 
        'blogs', 
        'servicios',
    'casos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'numero' => 'required|numeric',
            'color' => 'required|string',
            'icono_svg' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:200048',
        ]);

        $resultado = new Resultado();
        $resultado->titulo = $request->titulo;
        $resultado->numero = $request->numero;
        $resultado->color = $request->color;

         if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/resultados'), $nombreArchivo);
            $resultado->imagen = 'images/resultados/' . $nombreArchivo;
        }

        $resultado->save();


        $this->registrarMovimiento(
            'Crear',
            'Se creó un resultado: ' . $resultado->titulo,
            'resultados',
            $resultado->id
        );

        return redirect()->route('panel.landing.index')->with('success', 'Resultado creado correctamente');
    }

   public function update(Request $request, Resultado $resultado)
{
    $request->validate([
        'titulo' => 'required|string',
        'numero' => 'required|numeric',
        'color' => 'required|string',
        'icono_svg' => 'nullable|file|mimes:jpg,jpeg,png,svg,gif|max:2048',
    ]);

    $resultado->titulo = $request->titulo;
    $resultado->numero = $request->numero;
    $resultado->color = $request->color;
    //$resultado->updated_by = auth()->id();

    if ($request->hasFile('icono_svg')) {
        $file = $request->file('icono_svg');
        $nombreArchivo = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('images/resultados'), $nombreArchivo);
        $resultado->icono_svg = 'images/resultados/'.$nombreArchivo;
    }

    $resultado->save();

    Movimiento::create([
        //'usuario_id' => auth()->id(),
        'tipo_movimiento' => 'Actualizar',
        'descripcion' => 'Se actualizó el resultado: '.$resultado->titulo,
        'tabla_afectada' => 'resultados',
        'registro_id' => $resultado->id,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);

    return redirect()->route('panel.landing.index')->with('success', 'Resultado actualizado correctamente');
}


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
    // Guardar información de 'Quiénes Somos'
    public function storeQuienesSomos(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->only(['titulo', 'descripcion']);

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/quienes_somos'), $nombreArchivo);
            $data['imagen'] = 'images/quienes_somos/'.$nombreArchivo;
        }

        $quienes_somos = QuienesSomos::create($data);

        $this->registrarMovimiento(
            'Crear',
            'Se creó información de Quiénes Somos: '.$quienes_somos->titulo,
            'quienes_somos',
            $quienes_somos->id
        );

        return redirect()->route('panel.landing.index')->with('success', 'Información creada correctamente');
    }

    // Actualizar información de 'Quiénes Somos'
    public function updateQuienesSomos(Request $request, QuienesSomos $quienes_somos)
    {
        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $quienes_somos->titulo = $request->titulo;
        $quienes_somos->descripcion = $request->descripcion;

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/quienes_somos'), $nombreArchivo);
            $quienes_somos->imagen = 'images/quienes_somos/'.$nombreArchivo;
        }

        $quienes_somos->save();

        $this->registrarMovimiento(
            'Actualizar',
            'Se actualizó información de Quiénes Somos: '.$quienes_somos->titulo,
            'quienes_somos',
            $quienes_somos->id
        );

        return redirect()->route('panel.landing.index')->with('success', 'Información actualizada correctamente');
    }

     public function storeEncabezado(Request $request)
{
    $request->validate([
        //'titulo' => 'required|string|max:255',
        //'subtitulo' => 'required|string|max:255',
        //'contenido' => 'nullable|string',
        //'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        //'video_horizontal' => 'nullable|mimetypes:video/mp4,video/webm|max:2000000',
        //'video_vertical' => 'nullable|mimetypes:video/mp4,video/webm|max:2000000',
    ]);

    $encabezado = $request->only(['titulo', 'subtitulo', 'contenido']);
    $encabezado['created_by'] = Auth::id();
    $encabezado['updated_by'] = Auth::id();

    if ($request->hasFile('imagen')) {
        $file = $request->file('imagen');
        $nombreArchivo = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('images/banner'), $nombreArchivo);
        $encabezado['imagen'] = 'images/banner/'.$nombreArchivo;
    }

    if ($request->hasFile('video_horizontal')) {
        $video = $request->file('video_horizontal');
        $nombreVideo = time().'_'.$video->getClientOriginalName();
        $video->move(public_path('images/banner'), $nombreVideo);
        $encabezado['video_horizontal'] = 'images/banner/'.$nombreVideo;
    }

    if ($request->hasFile('video_vertical')) {
        $video = $request->file('video_vertical');
        $nombreVideo = time().'_'.$video->getClientOriginalName();
        $video->move(public_path('images/banner'), $nombreVideo);
        $encabezado['video_vertical'] = 'images/banner/'.$nombreVideo;
    }

    $encabezado = encabezado::create($encabezado);

    $this->registrarMovimiento('Crear', 'Se creó un encabezado: ' . $encabezado['titulo'], 'encabezados', $encabezado->id);

    //dd($encabezado);
   return redirect()->route('panel.landing.index')->with('success', 'Encabezado creado correctamente');
}

public function updateEncabezado(Request $request, Encabezado $encabezado)
        {

            // Actualizar textos
            // $encabezado->titulo = $request->titulo;
            // $encabezado->subtitulo = $request->subtitulo;
            // $encabezado->contenido = $request->contenido;

            // Video horizontal
            if ($request->hasFile('video_horizontal')) {
                $file = $request->file('video_horizontal');
                $nombreArchivo = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('images/banner'), $nombreArchivo);
                $encabezado->video_horizontal = 'images/banner/'.$nombreArchivo;
            }

            // Video vertical
            if ($request->hasFile('video_vertical')) {
                $file = $request->file('video_vertical');
                $nombreArchivo = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('images/banner'), $nombreArchivo);
                $encabezado->video_vertical = 'images/banner/'.$nombreArchivo;
            }

            $encabezado->save();

            // Registrar movimiento
            $this->registrarMovimiento(
                'Actualizar',
                'Se actualizó un encabezado: ' . $encabezado->titulo,
                'encabezados',
                $encabezado->id,
            );

            return redirect()->route('panel.landing.index')->with('success', 'Encabezado actualizado correctamente');
        }
    public function destroyEncabezado(encabezado $encabezado)
    {
        $encabezado->delete();

        $this->registrarMovimiento(
            'Eliminar',
            'Se eliminó un encabezado: ' . $encabezado->titulo,
            'encabezados',
            $encabezado->id,
        );

        return redirect()->route('panel.landing.index')->with('success', 'Encabezado eliminado correctamente');
    }

     public function indexCasosexito()
    {
        $casos = CasoExito::all();
        return view('panel.landing.contenido.casosExito', compact('casos'));
    }

    public function storeExito(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $casos = $request->only(['titulo', 'descripcion']);

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/casos_exito'), $nombreArchivo);
            $casos['imagen'] = 'images/casos_exito/' . $nombreArchivo;
        }
        //dd($data);
        $casos = CasoExito::create($casos);

        $this->registrarMovimiento(
            'Crear',
            'Se creó un caso de éxito: ' . $casos['titulo'],
            'casos_exito',
            $casos->id,
        );

        return redirect()->back()->with('success', 'Caso de éxito agregado correctamente.');
    }


    public function updateExito(Request $request, CasoExito $caso)
    {

        $caso->titulo = $request->titulo;
        $caso->descripcion = $request->descripcion;

        if ($request->hasFile('imagen')) {
            // eliminar imagen anterior
            if ($caso->imagen && file_exists(public_path($caso->imagen))) {
                unlink(public_path($caso->imagen));
            }

            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/casos_exito'), $nombreArchivo);
            $caso->imagen = 'images/casos_exito/' . $nombreArchivo;
        }

        $caso->save();

        $this->registrarMovimiento(
            'Actualizar',
            'Se actualizó un caso de éxito: ' . $caso->titulo,
            'casos_exito',
            $caso->id,
        );

        return redirect()->back()->with('success', 'Caso de éxito actualizado correctamente.');
    }

    public function destroyExito(CasoExito $caso)
    {
        if ($caso->imagen && file_exists(public_path($caso->imagen))) {
            unlink(public_path($caso->imagen));
        }

        $caso->delete();

        $this->registrarMovimiento(
            'Eliminar',
            'Se eliminó un caso de éxito: ' . $caso->titulo,
            'casos_exito',
            $caso->id,
        );

        return redirect()->back()->with('success', 'Caso de éxito eliminado correctamente.');
    }

    // Metodos para el blog

    public function createBlog(Request $request)
    {
       $request->validate([
           'titulo' => 'required|string|max:255',
           'contenido' => 'required|string',
       ]);

       $blog = $request->only(['titulo', 'contenido']);
       $blog['created_by'] = Auth::id();
       $blog['updated_by'] = Auth::id();
    $blog = Blog::create($blog);

       $this->registrarMovimiento(
           'Crear',
           'Se creó una entrada de blog: ' . $blog['titulo'],
           'blogs',
           $blog->id,
       );

         return redirect()->route('panel.landing.index')->with('success', 'Entrada de blog creada correctamente');

    }

    public function editBlog(Request $request, Blog $blog)
    {
        /*$request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
        ]);*/

        $blog->titulo = $request->titulo;
        $blog->contenido = $request->contenido;
         //   $blog->updated_by = Auth::id();
        $blog->save();


        $this->registrarMovimiento(
            'Actualizar',
            'Se actualizó una entrada de blog: ' . $blog->titulo,
            'blogs',
            $blog->id,
        );

        return redirect()->route('panel.landing.index')->with('success', 'Entrada de blog actualizada correctamente');
    }

    public function destroyBlog(Blog $blog)
    {
        $blog->delete();

        $this->registrarMovimiento(
            'Eliminar',
            'Se eliminó una entrada de blog: ' . $blog->titulo,
            'blogs',
            $blog->id,
        );

        return redirect()->route('panel.landing.index')->with('success', 'Entrada de blog eliminada correctamente');
    }

    // Metodos para servicios

    public function createServicios(Request $request)
    {
       $request->validate([
           'titulo' => 'required|string|max:255',
           'detalle' => 'required|string|max:255',
           'descripcion' => 'required|string',
           'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
       ]);

       $servicios = $request->only(['titulo', 'detalle', 'descripcion']);


       $servicios['created_by'] = Auth::id();
       $servicios['updated_by'] = Auth::id();

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/servicios'), $nombreArchivo);
            $servicios['imagen'] = 'images/servicios/'.$nombreArchivo;
        }

       $servicios = servicios::create($servicios);

         $this->registrarMovimiento(
              'Crear',
              'Se creó un servicio: ' . $servicios['titulo'],
              'servicios',
              $servicios->id,
         );

            return redirect()->route('panel.landing.index')->with('success', 'Servicio creado correctamente');

}

    public function editServicios(Request $request, servicios $servicios)
    {
        /*$request->validate([
            'titulo' => 'required|string|max:255',
            'detalle' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);*/

        $servicios->titulo = $request->titulo;
        $servicios->detalle = $request->detalle;
        $servicios->descripcion = $request->descripcion;
        //$servicios->updated_by = Auth::id();

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/servicios'), $nombreArchivo);
            $servicios->imagen = 'images/servicios/'.$nombreArchivo;
        }

        $servicios->save();


        $this->registrarMovimiento(
            'Actualizar',
            'Se actualizó un servicio: ' . $servicios->titulo,
            'servicios',
            $servicios->id,
        );

        return redirect()->route('panel.landing.index')->with('success', 'Servicio actualizado correctamente');
    }
public function updateServicios(Request $request, servicios $servicios)
{
    $request->validate([
        'titulo' => 'required|string|max:255',
        'detalle' => 'required|string|max:255',
        'descripcion' => 'required|string',
        'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $servicios->titulo = $request->titulo;
    $servicios->detalle = $request->detalle;
    $servicios->descripcion = $request->descripcion;

    if ($request->hasFile('imagen')) {
        $file = $request->file('imagen');
        $nombreArchivo = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('images/servicios'), $nombreArchivo);
        $servicios->imagen = 'images/servicios/'.$nombreArchivo;
    }

    $servicios->save();

    $this->registrarMovimiento(
        'Actualizar',
        'Se actualizó un servicio: ' . $servicios->titulo,
        'servicios',
        $servicios->id,
    );

    return redirect()->route('panel.landing.index')->with('success', 'Servicio actualizado correctamente');
}

    public function destroyServicios(servicios $servicios)
    {
        $servicios->delete();

        $this->registrarMovimiento(
            'Eliminar',
            'Se eliminó un servicio: ' . $servicios->titulo,
            'servicios',
            $servicios->id,
        );

        return redirect()->route('panel.landing.index')->with('success', 'Servicio eliminado correctamente');
    }

    // Revista Digital
       public function indexRev()
    {
        $exists = Storage::disk('local')->exists('private/revista.pdf');
        $updatedAt = $exists
            ? \Carbon\Carbon::createFromTimestamp(Storage::disk('local')->lastModified('private/revista.pdf'))
            : null;

        return view('panel.revista.links', compact('exists', 'updatedAt'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'hours' => 'nullable|integer|min:1|max:720', // hasta 30 días
        ]);

        $hours = (int) ($request->hours ?? 24);

        $token = Str::random(32);

        $link = URL::temporarySignedRoute(
            'revista.viewer',
            now()->addHours($hours),
            ['token' => $token]
        );

        return back()->with('link', $link);
    }
}
