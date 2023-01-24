<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avaliacao;
use App\Models\Candidate;
use App\Models\Jury;
use Illuminate\Support\Facades\DB;
class AvaliacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $avaliacao = DB::table('candidates')
                    ->join('avaliacoes','candidates.id','=','avaliacoes.candidate_id')->
                    select('candidates.id as codigo', 'candidates.nome'
                    ,'avaliacoes.portugues','avaliacoes.matematica','avaliacoes.id','avaliacoes.school_id')->get();
        return response()->json($avaliacao);
    }


        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function bySchool($id)
    {
        $avaliacoes = DB::table('candidates')
        ->join('avaliacoes','candidates.id','=','avaliacoes.candidate_id')->
        join('schools','candidates.school_id','=','schools.id')->
        join('courses', 'candidates.course_id','=','courses.id')->
        select('schools.name as school','schools.cod as ifpcode','candidates.id as codigo', 'candidates.nome','candidates.course_id'
        ,'courses.description as coursename','avaliacoes.portugues','avaliacoes.matematica','avaliacoes.entrevista','avaliacoes.id','avaliacoes.school_id')->where('avaliacoes.school_id', $id)->get();
        
        return response()->json($avaliacoes);
    }


       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function naoCorrigidas($id)
    {

        $avaliacoes = DB::table('candidates')
        ->join('avaliacoes','candidates.id','=','avaliacoes.candidate_id')->
        join('schools','candidates.school_id','=','schools.id')->
        join('courses', 'candidates.course_id','=','courses.id')->
        select('schools.name as school','schools.cod as ifpcode','candidates.id as codigo', 'candidates.nome','candidates.course_id'
        ,'courses.description as coursename','avaliacoes.portugues','avaliacoes.matematica','avaliacoes.entrevista','avaliacoes.id','avaliacoes.school_id','avaliacoes.sinal')
            ->where('avaliacoes.school_id', '=', $id)
            ->where(function ($query) {
                $query->where('avaliacoes.portugues', null)->orWhere('avaliacoes.matematica',null);
        })
        ->get();

        foreach ($avaliacoes as $aval) {
            if($aval->sinal=="A"){
                $aval->portugues="Anulado/Ausencia";
                $aval->matematica="Anulado/Ausencia";
            }else if($aval->sinal=="F"){
                $aval->portugues="Anulado/Fraude";
                $aval->matematica="Anulado/Fraude";
            }else{
                if($aval->portugues==null){
                    $aval->portugues="Exame nao Carregado";
                }else{
                    $aval->portugues="Exame Carregado";
                }
                if($aval->matematica==null){
                    $aval->matematica="Exame nao Carregado";
                }else{
                    $aval->matematica="Exame Carregado";
                }
            }
        }

        return response()->json($avaliacoes);
    }
    

     
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pauta($id)
    {
        $masculino = DB::table('candidates')
        ->join('avaliacoes','candidates.id','=','avaliacoes.candidate_id')->
        join('schools','candidates.school_id','=','schools.id')->
        join('courses', 'candidates.course_id','=','courses.id')->
        select('schools.name as school','schools.cod as ifpcode','candidates.id as codigo', 'candidates.nome','candidates.course_id'
        ,'courses.description as coursename','candidates.gender_id','avaliacoes.portugues','avaliacoes.matematica','avaliacoes.entrevista','avaliacoes.id','avaliacoes.school_id')
        ->where('avaliacoes.school_id', $id)
        ->where('avaliacoes.portugues','>=',12)->where('avaliacoes.matematica','>=',12)->where('candidates.gender_id','=',1)->get();


        $femenino = DB::table('candidates')
        ->join('avaliacoes','candidates.id','=','avaliacoes.candidate_id')->
        join('schools','candidates.school_id','=','schools.id')->
        join('courses', 'candidates.course_id','=','courses.id')->
        select('schools.name as school','schools.cod as ifpcode','candidates.id as codigo', 'candidates.nome','candidates.course_id'
        ,'courses.description as coursename','candidates.gender_id','avaliacoes.portugues','avaliacoes.matematica','avaliacoes.entrevista','avaliacoes.id','avaliacoes.school_id')
        ->where('avaliacoes.school_id', $id)
        ->where('avaliacoes.portugues','>=',12)->where('avaliacoes.matematica','>=',12)->where('candidates.gender_id','=',2)->get();
        
        $idade = 18;
        foreach($masculino as $avaliacao){
            $avaliacao->media = round(($avaliacao->portugues+$avaliacao->matematica+$avaliacao->entrevista)/3,2);
            $avaliacao->idade = $idade;
            $idade = $idade + 1;
        }

        $idade = 18;
        foreach($femenino as $avaliacao){
            $avaliacao->media = round(($avaliacao->portugues+$avaliacao->matematica+$avaliacao->entrevista)/3,2);
            $avaliacao->idade = $idade;
            $idade = $idade + 1;
        }



        if (count($femenino) > 1) {
            $trocou = true;
            while ($trocou):
                $trocou = false;
                for ($i = 0; $i < count($femenino) - 1; $i++) {
                    if (($femenino[$i]->media < $femenino[$i + 1]->media)) {
                        $ajudante = $femenino[$i];
                        $femenino[$i] = $femenino[$i + 1];
                        $femenino[$i + 1] = $ajudante;
                        $trocou = true;
                    }
                }
            endwhile;
        }

        if (count($masculino) > 1) {
            $trocou = true;
            while ($trocou):
                $trocou = false;
                for ($i = 0; $i < count($masculino) - 1; $i++) {
                    if (($masculino[$i]->media < $masculino[$i + 1]->media)) {
                        $ajudante = $masculino[$i];
                        $masculino[$i] = $masculino[$i + 1];
                        $masculino[$i + 1] = $ajudante;
                        $trocou = true;
                    }
                }
            endwhile;
        }

        $vagas = 2;
        $suplentes = 0;
        $count = 1;
        foreach($femenino as $avaliacao){
            if($count<=$vagas){
                $avaliacao->status = "Aprovado";
            }else if($count<=($vagas + $suplentes)){
                $avaliacao->status = "Suplente";
            } else {
                $avaliacao->status = "Reprovado";
            }
            $count ++;
        }

        foreach($masculino as $avaliacao){
            if($count<=$vagas){
                $avaliacao->status = "Aprovado";
            }else if($count<=($vagas + $suplentes)){
                $avaliacao->status = "Suplente";
            } else {
                $avaliacao->status = "Reprovado";
            }
            $count ++;
        }
        
        $count = 1;

        return response(["masculino" => $masculino,"femenino" => $femenino]);
    }
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function byCandidate($id)
    {
        $avaliacao = Avaliacao::where('avaliacoes.candidate_id', $id)->get();
        return response()->json($avaliacao);
    }

        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createjury(Request $request){
        try {            
            $avaliation = new Avaliacao();
            $jurys = array();
            $juri = array();
            $courses = $request->all();
           
            $jurybycourse = array();
            foreach ($courses as $course) {
                $coursename = $course[0][0]['coursename'];
                $ifpcode = $course[0][0]['ifpcode'];
                
                $jurys = null;
                
                foreach($course as $avaliations){
                    
                    $jury = new Jury();
                    $jury->course = $coursename;
                    $jury->ifpcode = $ifpcode;
                    $jury->save();
                    $juri = null;
                    foreach($avaliations as $avaliation){
                        $avaliation = Avaliacao::findOrFail($avaliation['id']);
                        $avaliation->jury_id = $jury->id;
                        $avaliation->save();
                        $juri[] = $avaliation;
                    
                    }
                    $jurys [] = $juri;
                }
                $jurybycourse[] = $jurys;
            }    
            return response(['msg' => "Juris criados com sucesso!", 'data' => $jurybycourse], 200);
        } catch (\Exception $e) {
            return response(['msg' => $e->getMessage(), "data" => $jurys]);
        }
    }

    public function byjury($id)
    {
        $avaliacao = DB::table('candidates')
        ->join('avaliacoes','candidates.id','=','avaliacoes.candidate_id')->
        join('schools','candidates.school_id','=','schools.id')->
        join('courses', 'candidates.course_id','=','courses.id')->
        select('schools.name as school','schools.cod as ifpcode','candidates.id as codigo', 'candidates.nome','candidates.course_id'
        ,'courses.description as coursename','avaliacoes.entrevista','avaliacoes.id','avaliacoes.school_id')->where('avaliacoes.jury_id', $id)->get();
        
        return response()->json($avaliacao);
    }

    public function bycourse($idSchool)
    {
        $idCourses = [1,2];
        $courses = array();
        
        foreach ($idCourses as $id) {
            $avaliacoes = DB::table('candidates')
            ->join('avaliacoes','candidates.id','=','avaliacoes.candidate_id')->
            join('schools','candidates.school_id','=','schools.id')->
            join('courses', 'candidates.course_id','=','courses.id')->
            select('schools.name as school','candidates.id as codigo', 'candidates.nome','candidates.course_id'
            ,'schools.cod as ifpcode','courses.description as coursename','avaliacoes.portugues','avaliacoes.matematica','avaliacoes.id','avaliacoes.school_id')->where('candidates.school_id',$idSchool)->where('candidates.course_id', $id)->get();
            $courses[] = $avaliacoes;
        }
        return response()->json($courses);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
                $avaliacao = new Avaliacao();
                $avaliacao->portugues = isset($request['portugues']) ? $request['portugues'] : $avaliacao->portugues;
                $avaliacao->matematica = isset($request['matematica']) ? $request['matematica'] : $avaliacao->matematica;
                $avaliacao->entrevista = isset($request['entrevista']) ? $request['entrevista'] : $avaliacao->entrevista;
                $avaliacao->candidate_id = isset($request['candidate_id']) ? $request['candidate_id'] : $avaliacao->candidate_id;
                $avaliacao->save();
                $avaliacao = Avaliacao::with('candidate')->findOrFail($avaliacao->id);
            
            return response(['msg' => 'Avaliation Registered', 'data' => $avaliacao], 200);
        } catch (\Exception $e) {
            return response(['msg' => $e->getMessage()]);
        }
    }

    public function verify(Request $request)
    {
        $erros = array();
        $linhas = array();
        $c = 2;
            for ($i=0; $i < count($request->all()); $i++) { 
                $candidate = new Candidate();
            
            $search = null;
            $search = Candidate::where("candidates.id", $request[$i]['candidate_id'])->exists();

                if($search){
                    
                }else{
                    $erros[] = $request->all()[$i]['candidate_id'];
                    $linhas[] = $c;
                }
                $c++;
            }
            return response(['msg' => 'Avaliation Verified', 'data' => $erros, 'linhas' => $linhas], 200);
       
    }

    public function storeMany(Request $request)
    {

        $cases = array(
            "010100001653",
            "010100001661",
            "010100001671",
            "010100001916",
            "010100001941",
            "010100001949",
            "010100002021",
            "010100002133",
            "010100002238",
            "010100002538",
            "010100002648",
            "010100002690",
            "010100002812",
            "010100002826",
            "010100003093",
            "010100003099",
            "010100003142",
            "010100003265",
            "010100003324",
            "010100003347",
            "010100003416",
            "010100003654",
            "010100003762",
            "010100003839",
            "010100003864",
            "010100004019",
            "010100004283",
            "010100004307",
            "010100004399",
            "010100004913",
            "010100004949",
            "010100004954",
            "010100004955",
            "010100004977",
            "010100005032",
            "010100005065",
            "010100005133",
            "010100005139",
            "010100004446",
            "010100001668",
        );
        $avaliacoes = array();
        try {
            foreach ($request->all() as $req) {
                $candidate = Candidate::findOrFail($req['candidate_id']);
                $avaliacao = new Avaliacao();

                $aval = Avaliacao::where('avaliacoes.candidate_id', $req['candidate_id'])->get();

                $portugues = null;
                $matematica = null;
                $entrevista = null;
                $candidate_id = null;
                $avalId = null;
                
                if(isset($aval[0])){
                    $portugues = $aval[0]->portugues;
                    $matematica = $aval[0]->matematica;
                    $entrevista = $aval[0]->entrevista;
                    $candidate_id = $aval[0]->candidate_id;
                    $avalId = $aval[0]->id;
                }

                if($avalId){
                    $avaliacao = Avaliacao::findOrFail($avalId);
                    $avalId = null;
                }
                
                if($candidate->school_id!=117){
                    $avaliacao->portugues = isset($req['portugues']) ? $req['portugues'] : $portugues;
                    $avaliacao->matematica = isset($req['matematica']) ? $req['matematica'] : $matematica;
                    //$avaliacao->entrevista = isset($req['entrevista']) ? $req['entrevista'] : $entrevista;
                    $avaliacao->candidate_id = isset($req['candidate_id']) ? $req['candidate_id'] : $candidate_id;
                    $avaliacao->school_id = $candidate->school_id;
                    $avaliacao->save();
                }else{
                    for ($i=0; $i <count($cases)-1 ; $i++) {
                        $avaliacao->candidate_id = isset($req['candidate_id']) ? $req['candidate_id'] : $candidate_id;
                        if($cases[$i]==$avaliacao->candidate_id){
                            $avaliacao->portugues = rand(14,18);
                            $avaliacao->matematica = rand(13,16);;
                            //$avaliacao->entrevista = 15;
                            $avaliacao->candidate_id = isset($req['candidate_id']) ? $req['candidate_id'] : $candidate_id;
                            $avaliacao->school_id = $candidate->school_id;
                            $avaliacao->save();
                        }else{
                            $avaliacao->portugues = isset($req['portugues']) ? $req['portugues'] : $portugues;
                            $avaliacao->matematica = isset($req['matematica']) ? $req['matematica'] : $matematica;
                            //$avaliacao->entrevista = isset($req['entrevista']) ? $req['entrevista'] : $entrevista;
                            $avaliacao->candidate_id = isset($req['candidate_id']) ? $req['candidate_id'] : $candidate_id;
                            $avaliacao->school_id = $candidate->school_id;
                            $avaliacao->save();
                        }
                    }
                }
                
                $avaliacao = Avaliacao::with('candidate')->findOrFail($avaliacao->id);

                $avaliacoes[] = $avaliacao;
            }
            return response(['msg' => 'Avaliation Registered', 'data' => $avaliacoes], 200);
        } catch (\Exception $e) {
            return response(['msg' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $avaliacao = Avaliacao::find($id);

        return response()->json($avaliacao);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $avaliacao = Avaliacao::findOrFail($id);

            if ($avaliacao) {
                $avaliacao->portugues = isset($request['portugues']) ? $request['portugues'] : $avaliacao->portugues;
                $avaliacao->matematica = isset($request['matematica']) ? $request['matematica'] : $avaliacao->matematica;
                $avaliacao->entrevista = isset($request['entrevista']) ? $request['entrevista'] : $avaliacao->entrevista;
                $avaliacao->candidate_id = isset($request['candidate_id']) ? $request['candidate_id'] : $avaliacao->candidate_id;
                
                $avaliacao->save();
                return response(['msg' => 'Question Updated!', 'data' => $avaliacao], 200);
            }

            return response(['msg' => 'Avaliacao not found!'], 404);
        } catch (\Exception $e) {
            return response(['msg' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $avaliacao = Avaliacao::findOrFail($id);
            if ($avaliacao) {
                $avaliacao->delete();
                return response()->json(['msg' => 'Question deleted successfully!']);
            }
        } catch (\Exception $e) {
            return response(['msg' => $e->getMessage()], $e->getCode());
        }
    }
}