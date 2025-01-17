<?php
if(isset($_GET['token'])){
date_default_timezone_set('America/Sao_Paulo');
$dados = file_get_contents("php://input");
file_put_contents('producao.json',$teste);
$dados = json_decode($dados);

		$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
); 
$cnpj_emitente = $dados->cnpj_emitente;
$token=hash('sha512',$cnpj_emitente.'PALAVRA_CHAVE');
if($token==$_GET['token']){
    
    
if($dados->cod_cancelamento == 1 ){
$codigo_cancelamento = 2;
}else{
  $codigo_cancelamento = 4; 
}


$inscricao_municipal=$dados->inscricao_municipal;
$numero_nfse=$dados->nota;

$xml_cabecalho = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <CancelarNfseRequest xmlns="http://ws.bhiss.pbh.gov.br">
               	<nfseCabecMsg xmlns="" >
               	<![CDATA[
               	<cabecalho versao="1.00" xmlns="http://www.abrasf.org.br/nfse.xsd"><versaoDados>1.00</versaoDados></cabecalho>
               	 ]]>
               	</nfseCabecMsg>
                    <nfseDadosMsg xmlns="" ><![CDATA[';
                     
$xml='<CancelarNfseEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">

<Pedido xmlns="http://www.abrasf.org.br/nfse.xsd">
<InfPedidoCancelamento Id="'.$numero_nfse.'">
<IdentificacaoNfse>
<Numero>'.$numero_nfse.'</Numero>
<Cnpj>'.$cnpj_emitente.'</Cnpj>
<InscricaoMunicipal>'.$inscricao_municipal.'</InscricaoMunicipal>
<CodigoMunicipio>3106200</CodigoMunicipio>
</IdentificacaoNfse>
<CodigoCancelamento>'.$codigo_cancelamento.'</CodigoCancelamento>
</InfPedidoCancelamento>
    </Pedido>                       
    </CancelarNfseEnvio>';
    
    $data = file_get_contents($dados->certificado_digital,false, stream_context_create($arrContextOptions));
$certPassword = $dados->senha_certificado;
        openssl_pkcs12_read($data, $certs, $certPassword);
        $err = openssl_error_string();
        if ($err) {
            throw new \Exception("ERRO AO LER O CERTIFICADO: ".$err);
        }
        
        $chave_privada_pkey = $certs['pkey'];
        $chave_publica_cert =$certs['cert'];
        $caminho_chaves_concatenada = sys_get_temp_dir().'/'.uniqid().'.pem';
        $caminho_chave_publica = sys_get_temp_dir().'/'.time().'.pem';
        $caminho_chave_privada = sys_get_temp_dir().'/k'.uniqid().'.pem';
        file_put_contents($caminho_chaves_concatenada, $chave_publica_cert.$chave_privada_pkey);
        file_put_contents($caminho_chave_publica, $chave_publica_cert);
        file_put_contents($caminho_chave_privada, $chave_privada_pkey);
        
    
  $xml=  XMLsign($xml, $tagid='InfPedidoCancelamento', $whiteSpace = false,$certPassword,$chave_privada_pkey,$chave_publica_cert);
    
        $xml_rodape=']]></nfseDadosMsg></CancelarNfseRequest></soap:Body></soap:Envelope>';



$url =  'https://bhissdigitalws.pbh.gov.br/bhiss-ws/nfse?wsdl';
$action='http://ws.bhiss.pbh.gov.br/CancelarNfse';
$xml = $xml_cabecalho.$xml.$xml_rodape;
         
$msgSize = strlen($xml);
$headers =['Content-Type: text/xml;charset=UTF-8',"SOAPAction:$action", "Content-length: $msgSize"];
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 120 + 20);
        curl_setopt($oCurl, CURLOPT_HEADER, 1);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 0);
        curl_setopt($oCurl, CURLOPT_SSLCERT, $caminho_chaves_concatenada);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($oCurl);
        $soapErr = curl_error($oCurl);
        $headSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
        curl_close($oCurl);
   
        $responseHead = trim(substr($response, 0, $headSize));
        $responseBody = trim(substr($response, $headSize));
        $erro =$responseBody;
     
$responseBody= str_replace('&gt;','>',$responseBody);
$responseBody= str_replace('&lt;','<',$responseBody);
$responseBody= str_replace('&quot;','"',$responseBody);
 $responseBody= substr($responseBody,186,-61);  
$xml =  simplexml_load_string($responseBody);



//print_r($xml);
if(isset($xml->RetCancelamento->NfseCancelamento->Confirmacao->Pedido->InfPedidoCancelamento->CodigoCancelamento)){
  $retorno= ['Resultado'=>  1];
   echo json_encode($retorno);
}else{
    
    print_r($erro);
}
     unlink($caminho_chaves_concatenada);
        unlink($caminho_chave_publica);
        unlink($caminho_chave_privada);
}else{
    
     echo 'TOKEN INVALIDO';
}
}else{
    
    echo 'ACESSO NEGADO';
}
function XMLsign($docxml, $tagid='', $whiteSpace = false,$senha,$chave_privada_pkey,$chave_publica_cert){
        $URLdsig='http://www.w3.org/2000/09/xmldsig#';
        $URLCanonMeth='http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $URLSigMeth='http://www.w3.org/2000/09/xmldsig#rsa-sha1';
        $URLTransfMeth_1='http://www.w3.org/2000/09/xmldsig#enveloped-signature';
        $URLTransfMeth_2='http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $URLDigestMeth='http://www.w3.org/2000/09/xmldsig#sha1';

        try{
            if ( $tagid == '' ){
                $msg = "Uma tag deve ser indicada para que seja assinada!!";
                throw new Exception($msg);
            }
            if ( $docxml == '' ){
                $msg = "Um xml deve ser passado para que seja assinado!!";
                throw new Exception($msg);
            }
            if (is_file($docxml)){
                $xml = file_get_contents($docxml);
            } else {
                $xml = $docxml;
            }

    
            $priKEY = $chave;
            $pubKEY =$certificado;

            // obter o chave privada para a ssinatura
            $prikeyid = openssl_pkey_get_private ($chave_privada_pkey , $senha );
            // limpeza do xml com a retirada dos CR, LF e TAB
            if (!$whiteSpace) {
                $order = array("\r\n", "\n", "\r", "\t");
                $replace = '';
                $xml = str_replace($order, $replace, $xml);
            }
            // Habilita a manipulação de erros da libxml
            libxml_use_internal_errors(true);
            //limpar erros anteriores que possam estar em memória
            libxml_clear_errors();
            // carrega o documento no DOM
            $xmldoc = new DOMDocument('1.0', 'utf-8');
            $xmldoc->preserveWhiteSpace = $whiteSpace; //preserva espaços em branco
            $xmldoc->formatOutput = false;
            // muito importante deixar ativadas as opções para limpar os espacos em branco
            // e as tags vazias
            $options = $whiteSpace?null:(LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            if ($xmldoc->loadXML($xml,$options)){
                $root = $xmldoc->documentElement;
            } else {
                $msg = "Erro ao carregar XML, provavel erro na passagem do par?metro docxml ou no pr?prio xml!!";
                $errors = libxml_get_errors();
                if (!empty($errors)) {
                    $i = 1;
                    foreach($errors as $error){
                        $msg .= "\n  [$i]-" . trim($error->message);
                    }
                    libxml_clear_errors();
                }
                throw new Exception($msg);
            }
            //extrair a tag com os dados a serem assinados
            $node = $xmldoc->getElementsByTagName($tagid)->item(0);
            if (!isset($node)){
                $msg = "A tag <$tagid> não existe no XML!!";
                throw new Exception($msg);
            }
            $id = trim($node->getAttribute("Id"));
            $idnome = preg_replace('/[^0-9]/','', $id);
            //extrai os dados da tag para uma string
            $dados = $node->C14N(false,false,NULL,NULL);
            //calcular o hash dos dados
            $hashValue = hash('sha1',$dados,true);
            //converte o valor para base64 para serem colocados no xml
            $digValue = base64_encode($hashValue);
            //monta a tag da assinatura digital
            $Signature = $xmldoc->createElementNS($URLdsig,'Signature');
            if (strtolower($tagid) == 'loterps'){
                $root->appendChild($Signature);
            } else {
                $node->parentNode->appendChild($Signature);
            }

            $SignedInfo = $xmldoc->createElement('SignedInfo');
            $Signature->appendChild($SignedInfo);
            //Cannocalization
            $newNode = $xmldoc->createElement('CanonicalizationMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', $URLCanonMeth);
            //SignatureMethod
            $newNode = $xmldoc->createElement('SignatureMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', $URLSigMeth);
            //Reference
            $Reference = $xmldoc->createElement('Reference');
            $SignedInfo->appendChild($Reference);
            $Reference->setAttribute('URI', '#'.$id);
            //Transforms
            $Transforms = $xmldoc->createElement('Transforms');
            $Reference->appendChild($Transforms);
            //Transform
            $newNode = $xmldoc->createElement('Transform');
            $Transforms->appendChild($newNode);
            $newNode->setAttribute('Algorithm', $URLTransfMeth_1);
            //Transform
            $newNode = $xmldoc->createElement('Transform');
            $Transforms->appendChild($newNode);
            $newNode->setAttribute('Algorithm', $URLTransfMeth_2);
            //DigestMethod
            $newNode = $xmldoc->createElement('DigestMethod');
            $Reference->appendChild($newNode);
            $newNode->setAttribute('Algorithm', $URLDigestMeth);
            //DigestValue
            $newNode = $xmldoc->createElement('DigestValue',$digValue);
            $Reference->appendChild($newNode);
            // extrai os dados a serem assinados para uma string
            $dados = $SignedInfo->C14N(false,false,NULL,NULL);
            //inicializa a variavel que irá receber a assinatura
            $signature = '';
            //executa a assinatura digital usando o resource da chave privada
            $resp = openssl_sign($dados,$signature,$prikeyid);
            //codifica assinatura para o padrao base64
            $signatureValue = base64_encode($signature);
            //SignatureValue
            $newNode = $xmldoc->createElement('SignatureValue',$signatureValue);
            $Signature->appendChild($newNode);
            //KeyInfo
            $KeyInfo = $xmldoc->createElement('KeyInfo');
            $Signature->appendChild($KeyInfo);
            //X509Data
            $X509Data = $xmldoc->createElement('X509Data');
            $KeyInfo->appendChild($X509Data);
            //carrega o certificado sem as tags de inicio e fim
            $cert = str_replace("\r", '', $chave_publica_cert);
            $cert = str_replace("\n", '', $cert);
            $cert = str_replace('-----BEGIN CERTIFICATE-----', '', $cert);
            $cert = wordwrap(trim(str_replace('-----END CERTIFICATE-----', '', $cert)), 64, "\n", true);
            //X509Certificate
            $newNode = $xmldoc->createElement('X509Certificate',$cert);
            $X509Data->appendChild($newNode);
            //grava na string o objeto DOM
            if($tagid=='InfRps'){
                $xml = $xmldoc->saveXML($xmldoc->documentElement);
            }else{
            $xml = $xmldoc->saveXML();
            }
            // libera a memoria
            openssl_free_key($prikeyid);
        } catch (Exception $e) {
            throw $e;
            return false;
        }
        //retorna o documento assinado
        return $xml;
    } 
