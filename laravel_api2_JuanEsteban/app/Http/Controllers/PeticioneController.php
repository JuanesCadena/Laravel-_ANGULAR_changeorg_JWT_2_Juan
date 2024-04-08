<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Peticione;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class PeticioneController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api', ['except' => ['index', 'show', 'list']]);
    }

    public function index(Request $request)
    {
        $peticiones = Peticione::all()->load(['user', 'categoria', 'files']);
        return $peticiones;
    }

    public function listMine(Request $request)
    {
        $user = Auth::user();
        $peticiones = Peticione::all()->where('user_id', $user->id);
        return $peticiones;
    }

    public function show(Request $request, $id)
    {
        $peticion = Peticione::findOrFail($id);
        return $peticion;
    }

    public function update(Request $request, $id)
    {
        $peticion = Peticione::findOrFail($id);
        if ($request->user()->cannot('update', $peticion)){
            return response()->json(['message' => 'No estás autorizado para actualizar'], 403);
        }
        $res =$peticion->update($request->all());
        if ($res){
            return response()->json(['message' => 'Petición actualizada satisfactoriamente', 'peticion' => $peticion, 201]);
        }
        return response()->json(['message' => 'Error actualizando la petición', 500]);

    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'titulo' => 'required|max:255',
                'descripcion' => 'required',
                'destinatario' => 'required',
                'categoria_id' => 'required',
                //'file' => 'required',
            ]);


        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],401);
        }
        $validator = Validator::make($request->all(),
            [
                'file' => 'required|mimes:png,jpg|max:4096',
            ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        if ($file = $request-> file('file')){
            $name = $file->getClientOriginalName();
            Storage::put($name, file_get_contents($request->file('file')->getRealPath()));
            $file->move('storage/',$name);
            $input['file']=$name;
        }
        $category = Categoria::findOrFail($request->input('categoria_id'));
        $user = Auth::user();
        $user = User::findOrFail($user->id);

        $peticion = new Peticione();
        $peticion->titulo = $request->input('titulo');
        $peticion->descripcion = $request->input('descripcion');
        $peticion->destinatario = $request->input('destinatario');
        //$peticion->image = $input['file'];


        $peticion->user()->associate($user);
        $peticion->categoria()->associate($category);

        $peticion->firmantes = 0;
        $peticion->estado = 'pendiente';
        $res = $peticion->save();

        $imgdb = new File();
        $imgdb-> name = $input['file'];
        $imgdb->file_path='storage/'.$input['file'];
        $imgdb->peticione_id=$peticion->id;
        $imgdb->save();


        if($res){
            return response()->json(['message'=>'Peticion creada satisfactoriamente','peticion'=>$peticion],201);
        }
        return response()->json(['message'=>'Error creando la peticion'],500);
    }

    public function fileUpload(Request $res, $peticion_id =null){
        $file = $res->file('file');

    }


    public function firmar(Request $request, $id)
    {

        try{
            $peticion = Peticione::findOrFail($id);
            $user= Auth::user();
            $user_id = [$user->id];
            $peticion->firmas()->attach($user_id);
            $peticion->firmantes = $peticion->firmantes + 1;
        }catch (\throwable$th){
            return response()->json(['message' => 'La peticion no se ha podido firmar'], 500);
        }
        if ($peticion->firmas()) {
            return response()->json(['message' => 'Peticion firmada satisfactoriamente', 'peticion' =>$peticion, 201 ]);
        }
        return response()->json(['message' => 'La peticion no se ha podido firmar'], 500);
        $peticion->save();
        return $peticion;
    }

    public function cambiarEstado(Request $request, $id){
        $peticion = Peticione::findOrFail($id);
        if ($request->user()->cannot('cambiarEstado', $peticion)){
            return response()->json(['message' => 'No estás autorizado'], 403);
        }
        $peticion->estado = 'aceptada';
       $res = $peticion->save();
        if ($res){
            return response()->json(['message'=>'Peticion actualizada satisfactoriamente','peticion'=>$peticion],201);
        }
        return response()->json(['message'=>'Error actualizando la peticion'],500);
    }

    public function peticionesFirmadas(Request $request)
    {
        try {
            $id = Auth::id();
            $usuario = User::findOrFail($id);
            $peticiones = $usuario->firmas;
            return response()->json( $peticiones, 200);
}catch (\Exception $exception){
            return response()->json( ['error'=>$exception->getMessage()], 500);
}
    }

    public function delete(Request $request, $id)
    {
        $peticion = Peticione::findOrFail($id);
        $res = $peticion->delete();

        if ($res) {
            return response()->json(['message' => 'Peticion creada satisfactoriamente'], 201);
        }
        return response()->json(['message' => 'Error eliminando la peticion'], 500);
    }

}
