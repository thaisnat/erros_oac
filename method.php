<?php

//RECEBIMENTOS DE DADOS
$data 	    = (isset($_POST["data"]))      ? $_POST["data"]   	: "";
$method 	= (isset($_POST["method"]))    ? $_POST["method"] 	: "";
$emitter	= (isset($_POST["emitter"]))   ? $_POST["emitter"] 	: "";
$note;

//DETERMINAÇÃO DO MÉTODO SOLICITADO
switch($method){
    case "01":
        $note = "";
        $response["DADO_EMITIDO"] 	= emissorparidadesimples($data);
        $response["DADO_RECEBIDO"] 	= (empty($emitter)) ? $response["DADO_EMITIDO"] : $emitter ;
        $response["DADO_IS_VALID"] 	= receptorparidadesimples($response["DADO_RECEBIDO"]);
        $response["OBSERVACAO"] 	= substr($note, 5);
        break;
    case "02":
        $note = "";
        $response["DADO_EMITIDO"] 	= emissorhamming($data);
        $response["DADO_RECEBIDO"] 	= (empty($emitter)) ? $response["DADO_EMITIDO"] : $emitter ;
        $response["DADO_IS_VALID"] 	= receptorhamming($response["DADO_RECEBIDO"]);
        $response["OBSERVACAO"] 	= substr($note, 5);
        break;
    case "03":
        $note = "";
        $response["DADO_EMITIDO"] 	= emissorcrc($data);
        $response["DADO_RECEBIDO"] 	= (empty($emitter)) ? $response["DADO_EMITIDO"] : $emitter ;
        $response["DADO_IS_VALID"] 	= receptorcrc($response["DADO_RECEBIDO"]);
        $response["OBSERVACAO"] 	= substr($note, 5);
        break;
    default:
        $response["OBSERVACAO"] 	= "</br>MÉTODO NÃO IDENTIFICADO!";
}

//RESPOSTA
echo json_encode($response);

//FUNÇÕES QUE SIMULAM ALGUNS MÉTODOS UTILIZADOS PELA CAMADA DE ENLACE PARA DETECÇÃO E CORREÇÃO DE ERROS

//DETECÇÃO DE ERROS

//01 - PARIDADE SIMPLES

//EMISSOR - PARIDADE SIMPLES (PAR)
function emissorparidadesimples($bin){

    global $note;
    $array = str_split($bin);
    if(array_sum($array) % 2 == 0){
        array_push($array,0);
    } else {
        array_push($array,1);
    }

    return implode("",$array);

}

//RECEPTOR - PARIDADE SIMPLES (PAR)
function receptorparidadesimples($bin){

    global $note;
    $return = false;
    $array = str_split($bin);
    if(array_sum($array) % 2 != 0){
        $note .= "</br><b>ERRO DETECTADO:</b> O BLOCO ESTA CORROMPIDO";
        $return = true;
    } else {
        $note .= "</br><b>BLOCO RECEBIDO SEM ERROS:</b> TIPO - PARIDADE SIMPLES";
    }

    return $return;
    
}

//CORREÇÃO DE ERROS

//02 - HAMMING

//EMISSOR - HAMMING
function emissorhamming($bin){

    global $note;    
    $position = array(0,1,3,7,15,31);
    $array = str_split($bin);
    $array = array_reverse($array);
    foreach($position as $pos){
        if(count($array) >= $pos){
            array_splice($array, $pos, 0, "R");
        }
    }

    $string = implode("",$array);

    foreach($position as $pos){
        if(count($array) >= $pos){
            $auxiliar = str_split($string, ($pos + 1));
            
            for($i = 1; $i < count($auxiliar); $i +=2){
                unset($auxiliar[$i]);    
            }

            if(array_sum(str_split(substr(implode("",$auxiliar), 1))) % 2 == 0){
                $array[$pos] = 0;
            } else {
                $array[$pos] = 1;
            }

            $string = substr($string, ($pos + 1));
        }
    }
    
    return implode("",array_reverse($array));

}

//RECEPTOR - HAMMING
function receptorhamming($bin){

    global $note;    
    $position = array(0,1,3,7,15,31);
    $array = str_split($bin);
    $array = array_reverse($array);

    $string = implode("",$array);
    $analise = array();
    foreach($position as $pos){
        if(count($array) >= $pos){
            $auxiliar = str_split($string, ($pos + 1));
            
            for($i = 1; $i < count($auxiliar); $i +=2){
                unset($auxiliar[$i]);    
            }

            if(array_sum(str_split(implode("",$auxiliar))) % 2 == 0){
                array_unshift($analise, 0);
            } else {
                array_unshift($analise, 1);
            }

            $string = substr($string, ($pos + 1));
        }
    }
    
    $position_erro = bindec(implode("",$analise));
    
    if($position_erro != 0){

        $array[$position_erro - 1] = ($array[$position_erro - 1] == 0) ? 1 : 0 ;
        $bin_corrigido = implode("",array_reverse($array));    

        $note .= "</br><b>ERRO DETECTADO POR HAMMING:</b> POSIÇÃO {$position_erro} CORROMPIDA";
        $note .= "</br><b>BINÁRIO EMITIDO:</b> {$bin}";
        $note .= "</br><b>BINÁRIO CORRIGIDO:</b> {$bin_corrigido}";

    } else {

        $note .= "</br><b>BLOCO RECEBIDO SEM ERROS:</b> TIPO - HAMMING";

    }

    return (boolean) $position_erro;
}

//03 - CYCLIC REDUNDANCY CHECK (CRC)

//EMISSOR - CRC
function emissorcrc($bin){

    global $note;
    $dividendo = $bin."000";
    $array = str_split($dividendo);
    $divisor = "1101";
    $quociente = "1";
    $resto = "";
    $auxiliar = $array[0].$array[1].$array[2].$array[3];

    for($i = 4; $i <= count($array); $i++){
        $resto = decbin(bindec($auxiliar) ^ bindec($divisor));
        $resto .= $array[$i];
        $resto = str_pad($resto, 4, "0", STR_PAD_LEFT);
        $auxiliar = $resto;
        if(substr($resto,0,1) == "0"){
            $divisor = "0000";
            $quociente .= "0";
        } else {
            $divisor = "1101";
            $quociente .= "1";
        }
    }

    $quociente = substr($quociente, 0, -1);
    $resto = substr($resto, -3);

    $note .= "</br><b>EMISSOR CRC</b>";
    $note .= "</br><b>BINÁRIO:</b> {$bin}";
    $note .= "</br><b>RESTO:</b> {$resto}";
    $note .= "</br><b>RESULTADO DA EMISSAO:</b> {$bin}{$resto}";

    return $bin.$resto;

}

//RECEPTOR - CRC
function receptorcrc($bin){

    global $note;
    $array = str_split($bin);
    $divisor = "1101";
    $quociente = "1";
    $resto = "";
    $auxiliar = $array[0].$array[1].$array[2].$array[3];

    for($i = 4; $i <= count($array); $i++){
        $resto = decbin(bindec($auxiliar) ^ bindec($divisor));
        $resto .= $array[$i];
        $resto = str_pad($resto, 4, "0", STR_PAD_LEFT);
        $auxiliar = $resto;
        if(substr($resto,0,1) == "0"){
            $divisor = "0000";
            $quociente .= "0";
        } else {
            $divisor = "1101";
            $quociente .= "1";
        }
    }
  
    $quociente = substr($quociente, 0, -1);
    $resto = substr($resto, -3);
    $dado = substr($bin, 0, -3);

    $note .= "</br></br><b>RECEPTOR CRC</b>";
    $note .= "</br><b>BINÁRIO:</b> {$bin}";
    $note .= "</br><b>RESTO:</b> {$resto}";
    $note .= "</br><b>DADOS RECEBIDOS:</b> {$dado}";

    return (boolean) bindec($resto);

}