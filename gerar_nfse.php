<?php
header('Content-Type: text/html; charset=utf-8');
if(isset($_GET['token'])){
date_default_timezone_set('America/Sao_Paulo');
$dados = file_get_contents("php://input");
$dados = mb_convert_encoding($dados, 'UTF-8');
$teste =$dados;
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
$cnae=$dados->cnae;
$logo=$dados->logo;
$inscricao_municipal=$dados->inscricao_municipal;
$numero_rps=$dados->rps_num;
$serie=$dados->serie;
$tipo=1;
$data_emissao=date('Y-m-d').'T'.date('H:i:s');
$competencia=date('Y-m-d');
$valor_servico=$dados->valor;
$valor_base=$dados->valor_base;
$codigo_item=$dados->codigo_servico;
$cnpj_cliente=$dados->tomador_cnpj;
$endereco_cliente=$dados->tomador_logradouro;
//$endereco_cliente = preg_replace('/[^a-zA-Z0-9 ]/', '',$endereco_cliente);
$bairro_cliente=$dados->tomador_bairro;
//$bairro_cliente=preg_replace('/[^a-zA-Z0-9 ]/', '', $bairro_cliente);
$numero_cliente=$dados->tomador_numero_logradouro;
$codigo_municipio_cliente=$dados->codigo_municipio;
$descrica_servico=$dados->descricao_servico;
$uf_cliente=$dados->uf_cliente;
$cep_cliente=$dados->tomador_CEP;
$email_cliente=$dados->tomador_email;
$telefone_cliente=$dados->telefone;
$codigo_municipio_empresa=$dados->cod_municipio_prestacao_servico;
$tomador_complemento =$dados->tomador_complemento;
$tomador_inscricao_municipal=$dados->tomador_inscricao_municipal;
$CodigoTributacaoMunicipio=1304;
$ResponsavelRetencao='';
$IssRetido=2;
$aliquata_servicos='';


if($tomador_complemento <>''){
  $tomador_complemento="<Complemento>$tomador_complemento</Complemento>";
}
if($email_cliente<>''){
   $email_cliente= "<Email>'.$email_cliente.'</Email>";
    
}
if(strlen($cnpj_cliente)==14){
  $cnpj_cliente="<Cnpj>$cnpj_cliente</Cnpj>";  
}else{
   $cnpj_cliente="<Cpf>$cnpj_cliente</Cpf>";    
}
$aliquata_iss='';
if($dados->enquadramento_tributario > 2){
    $OptanteSimplesNacional=2;

     $RegimeEspecialTributacao='';
     $aliquata_iss='<Aliquota>'.($dados->aliquota_issqn/100).'</Aliquota>';
}else{
   $OptanteSimplesNacional=1;
   //$AliquotaServicos='<AliquotaServicos>'.$dados->aliquota_issqn.'</AliquotaServicos>';
   $RegimeEspecialTributacao="<RegimeEspecialTributacao>6</RegimeEspecialTributacao>";
}
$aliquota_iss =$dados->aliquota_issqn;
$valor_iss=$valor_base /100 * $aliquota_iss;
if($dados->tributacao_operacao==2){
    
    $IssRetido=1;
    $ResponsavelRetencao= '<ResponsavelRetencao>1</ResponsavelRetencao>';
     $aliquata_iss='<Aliquota>'.($dados->aliquota_issqn/100).'</Aliquota>';
     //$AliquotaServicos ='<AliquotaServicos>'.$dados->aliquota_issqn.'</AliquotaServicos>';
}
$nome_tomador=substr($dados->tomador_razao_social,0,114);
$id_rps = 'rps'.$numero_rps;
$nome_tomador=preg_replace('/[^a-zA-Z0-9 ]/', '',$nome_tomador); 
                 $xml='<Rps>
                        <InfRps xmlns="http://www.abrasf.org.br/nfse.xsd" Id="'.$id_rps.'" >
                        <IdentificacaoRps>
                        <Numero>'.$numero_rps.'</Numero>
                        <Serie>'.$serie.'</Serie>
                        <Tipo>1</Tipo>
                        </IdentificacaoRps>
                        <DataEmissao>'.$data_emissao.'</DataEmissao>
                        <NaturezaOperacao>1</NaturezaOperacao>
                         '.$RegimeEspecialTributacao.'
                        <OptanteSimplesNacional>'.$OptanteSimplesNacional.'</OptanteSimplesNacional>
                        <IncentivadorCultural>2</IncentivadorCultural>
                        <Status>1</Status>
                        <Servico>
                            <Valores>
                                <ValorServicos>'.$valor_servico.'</ValorServicos>
                                <IssRetido>'.$IssRetido.'</IssRetido>
                                <ValorIss>'.$valor_iss.'</ValorIss>'.
                                $aliquata_iss.'
                            </Valores>
                            <ItemListaServico>'.$codigo_item.'</ItemListaServico>
                            <CodigoTributacaoMunicipio>130500488</CodigoTributacaoMunicipio>
                            <Discriminacao>'.$descrica_servico.'</Discriminacao>
                            <CodigoMunicipio>'.$codigo_municipio_empresa.'</CodigoMunicipio>
                        </Servico>
                            <Prestador>
                                <Cnpj>'.$cnpj_emitente.'</Cnpj>
                                <InscricaoMunicipal>'.$inscricao_municipal.'</InscricaoMunicipal>
                            </Prestador>
                        <Tomador>
                            <IdentificacaoTomador>
                                <CpfCnpj>
                                '.$cnpj_cliente.'
                                </CpfCnpj>
                            </IdentificacaoTomador>
                            <RazaoSocial>'.$nome_tomador.'</RazaoSocial>
                            <Endereco>
                                <Endereco>'.$endereco_cliente.'</Endereco>
                                <Numero>'.$numero_cliente.'</Numero>
                                '.$tomador_complemento.'
                                <Bairro>'.$bairro_cliente.'</Bairro>
                                <CodigoMunicipio>'.$codigo_municipio_cliente.'</CodigoMunicipio>
                                <Uf>'.$uf_cliente.'</Uf>
                                <Cep>'.$cep_cliente.'</Cep>
                            </Endereco>
                            <Contato>
                                 <Telefone>'.$telefone_cliente.'</Telefone>
                                 '.$email_cliente.'
                            </Contato>
                        </Tomador>
                    </InfRps>
                </Rps>';


      
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
        

$wsdl = 'https://bhissdigitalws.pbh.gov.br/bhiss-ws/nfse?wsdl';


$xml=XMLsign($xml, $tagid='InfRps', $whiteSpace = false,$certPassword,$chave_privada_pkey,$chave_publica_cert);
$nfseCabecMsg = '<?xml version="1.0" encoding="UTF-8"?><cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="1.00" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><versaoDados>1.00</versaoDados></cabecalho>';

$nfseDadosMsg = '<GerarNfseEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
            <LoteRps xmlns="http://www.abrasf.org.br/nfse.xsd" Id="L2025" versao="1.00" >
                <NumeroLote>'.$numero_rps.'</NumeroLote>
                <Cnpj>'.$cnpj_emitente.'</Cnpj>
                <InscricaoMunicipal>'.$inscricao_municipal.'</InscricaoMunicipal>
                <QuantidadeRps>1</QuantidadeRps>
                <ListaRps>'.$xml.'</ListaRps>
            </LoteRps>
        </GerarNfseEnvio>';

$nfseDadosMsg=XMLsign($nfseDadosMsg, $tagid='LoteRps', $whiteSpace = false,$certPassword,$chave_privada_pkey,$chave_publica_cert);

try {
    // Configurações do cliente SOAP
    $options = [
        'trace' => true,                  // Habilita rastreamento para depuração
        'exceptions' => true,             // Lança exceções em caso de erro
        'local_cert' => $caminho_chaves_concatenada,// Certificado digital no formato PEM - CHAVE PUBLICA(CERT) E CHAVE PRIVADA(pkey) Concatenada
        'passphrase' => $certPassword,           // Senha do certificado
        'cache_wsdl' => WSDL_CACHE_NONE,  // Desativa cache 
        'encoding' => 'UTF-8',            // Define o encoding
    ];

    $client = new SoapClient($wsdl, $options);
    $params = [
        'nfseCabecMsg' => $nfseCabecMsg,
        'nfseDadosMsg' => $nfseDadosMsg,
    ];
    $response = $client->__soapCall('GerarNfse', [$params]);
    
    
   // print_r($response);
 $xml_retorno= $response->outputXML;

    $xmlObject = simplexml_load_string($xml_retorno);
      if(isset($xmlObject->ListaMensagemRetornoLote->MensagemRetorno)){
       //  echo json_encode($xmlObject->ListaMensagemRetornoLote->MensagemRetorno);
          echo 'Erro '.$xmlObject->ListaMensagemRetornoLote->MensagemRetorno->Codigo;
          echo '<br>';
          echo $xmlObject->ListaMensagemRetornoLote->MensagemRetorno->Mensagem;
      }elseif($xmlObject->ListaMensagemRetorno->MensagemRetorno){
          echo 'Erro '.$xmlObject->ListaMensagemRetorno->MensagemRetorno->Codigo;
          echo '<br>';
          echo $xmlObject->ListaMensagemRetorno->MensagemRetorno->Mensagem;
          
      }else{
          
        $xml = new SimpleXMLElement($xml_retorno);
        
        $namespace = $xml->getNamespaces(true);
        if(isset($xml->ListaNfse->CompNfse)){
            

        $nfse = $xml->ListaNfse->CompNfse->children($namespace['']);
  
        $numero = (string) $nfse->Nfse->InfNfse->Numero;
        $codigoVerificacao = (string) $nfse->Nfse->InfNfse->CodigoVerificacao;
        $diretorio = 'arquivos_xml/'.$cnpj_emitente.'/'.date('Y').'/'.$numero.'-nfse.xml';
            grava_xml($cnpj_emitente,$diretorio,$xml_retorno);
            $link='https://'.$_SERVER['HTTP_HOST'].'/nfse/bh/danfe.php';
            $link.='?logo='.$logo.'&numero_nfse='.$numero.'&cnpj='.$cnpj_emitente.'&autenticidade='.$autenticidade;
            $link.='&token='.$token.'&ano='.date('Y').'&inscricao_municipal='.$inscricao_municipal;
            $retorno = ['Resultado'=>'1','Nota'=>$numero,'autenticidade'=>$codigoVerificacao,'LinkImpressao'=>$link];
             echo json_encode($retorno);
             
        }else{
            
             print_r($response);
        }
            }
            
        unlink($caminho_chaves_concatenada);
        unlink($caminho_chave_publica);
        unlink($caminho_chave_privada);
        

} catch (SoapFault $e) {
    echo "Erro SOAP: " . $e->getMessage() . "\n";
}
}else{
    
    echo 'TOKEN INVALIDO';
}

}else{
    echo 'ACESSO NEGADO';
    
}
    /// FUNÇÂO DISPONIBILIZADA POR  luizvaz, fiz poucas alterações
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
    } //fim signXML
function grava_xml($num_inscricao,$diretorio,$xml )
{
	$index= "<?php 
						 echo 'usuário não autorizado';
?> 
						 ";

		$diretorio_inicial = 'arquivos_xml/';
      
	if(!is_dir($diretorio_inicial.$num_inscricao)){

		mkdir($diretorio_inicial.$num_inscricao, 0777);
		$caminho_index = $diretorio_inicial.$num_inscricao.'/index.php';
		file_put_contents($caminho_index,$index);
	}
	
	if(!is_dir($diretorio_inicial.$num_inscricao.'/'.date('Y'))){
		mkdir($diretorio_inicial.$num_inscricao.'/'.date('Y'), 0777);
		$caminho_index = $diretorio_inicial.$num_inscricao.'/'.date('Y').'/index.php';
		file_put_contents($caminho_index,$index);

	}

	file_put_contents($diretorio,$xml);
}


