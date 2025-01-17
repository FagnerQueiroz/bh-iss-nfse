<?php
///arquivo danfe baseado nas necessidades da minha transmissa. ajuste para a sua.
if(isset($_GET['numero_nfse']) and isset($_GET['cnpj']) and isset($_GET['autenticidade']) and  isset($_GET['token'])  ){
    if($_GET['token'] == hash('sha512',$_GET['cnpj'].'PALAVRA_CHAVE') ){


$arrContextOptions=array(
	"ssl"=>array(
		"verify_peer"=>false,
		"verify_peer_name"=>false,
	),
); 
$cnpj_emitente = $_GET['cnpj'];
$inscricao_municipal=$_GET['inscricao_municipal'];
$numero_nfe=$_GET['numero_nfse'];
$ano=$_GET['ano'];
$logo=$_GET['logo'];
$arquivo_xml='arquivos_xml/'.$cnpj_emitente.'/'.$ano.'/'.$numero_nfe.'-nfse.xml';

if($_GET['situacao'] ==9){
    $data_cancelamento= date('d/m/Y',strtotime($_GET['data']));
$div_cancelamento='<div id="cancelada" class="overlay" style="filter: progid:DXImageTransform.Microsoft.Matrix(sizingMethod=\'auto expand\', M11=0.7071067811865476, M12=0.7071067811865475, M21=-0.7071067811865475, M22=0.7071067811865476); filter: alpha(opacity=40);">
					<p style="font-size: 72px;">CANCELADA</p>
			<a id="motivoCancelamento" href=\'https://bhissdigital.pbh.gov.br/nfse/pages/consultaNFS-e_cidadao_creditoIPTU.jsf\'
target="_blank"  style="font-size:24px;margin-bottom:5px;color:red">EM '.$data_cancelamento.' - CLIQUE AQUI PARA VERIFICAR CANCELAMENTO DA NFS-e '.$numero_nfe.'</p></div>';
}

if(is_file($arquivo_xml)){
$responseBody= file_get_contents($arquivo_xml);
}else{

$xml_cabecalho = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<ConsultarNfseRequest xmlns="http://ws.bhiss.pbh.gov.br">
<nfseCabecMsg xmlns="" >
<![CDATA[
<cabecalho versao="1.00" xmlns="http://www.abrasf.org.br/nfse.xsd"><versaoDados>1.00</versaoDados></cabecalho>
]]>
</nfseCabecMsg>
<nfseDadosMsg xmlns="" ><![CDATA[';
$xml='<ConsultarNfseEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
<Prestador>
<Cnpj>'.$_GET['cnpj'].'</Cnpj>
<InscricaoMunicipal>'.$inscricao_municipal.'</InscricaoMunicipal>
</Prestador>
<NumeroNfse>'.$numero_nfe.'</NumeroNfse>
</ConsultarNfseEnvio>';
$xml_rodape=']]></nfseDadosMsg></ConsultarNfseRequest></soap:Body></soap:Envelope>';
$url =  'https://bhissdigitalws.pbh.gov.br/bhiss-ws/nfse?wsdl';
$action='http://ws.bhiss.pbh.gov.br/ConsultarNfsePorFaixa';
$pemPath='certificado_chave.pem';
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
curl_setopt($oCurl, CURLOPT_SSLCERT, $pemPath);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($oCurl, CURLOPT_POST, true);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xml);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($oCurl);

$soapErr = curl_error($oCurl);
$headSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
$httpCode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
curl_close($oCurl);
$responseBody= str_replace('&gt;','>',$response);
$responseBody= str_replace('&lt;','<',$responseBody);
$responseBody= str_replace('&quot;','"',$responseBody);
$responseBody= substr($responseBody,349,-62);  
}
$xml = simplexml_load_string($responseBody, "SimpleXMLElement", LIBXML_NOCDATA);
$numero = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->Numero;
$ano = substr($numero,0,4);
$numero=(int)substr($numero,4);
$numero=$ano.'/'.$numero;
$dataEmissao = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->DataEmissao;
$codigoVerificacao = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->CodigoVerificacao;
$razaoSocialPrestador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->RazaoSocial;
$cnpjPrestador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->IdentificacaoPrestador->Cnpj;
$cnpjPrestador=mascara('##.###.###/####-##',$cnpjPrestador);
$inscricaoMunicipalPrestador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->IdentificacaoPrestador->InscricaoMunicipal;
$enderecoPrestador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->Endereco->Endereco . ', ' .
(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->Endereco->Numero . ', ' .
(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->Endereco->Bairro . ' - Cep: ' .
(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->Endereco->Cep;
$enderecoTomador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Endereco->Endereco . ', ' .
(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Endereco->Numero . ', ' .
(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Endereco->Bairro . ' - Cep: ' .
(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Endereco->Cep;
$CodigoMunicipio=(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Endereco->CodigoMunicipio;
$municipios=json_decode(file_get_contents('arquivos/municipios.json')); 
$cidadeTomador='';
$municipios = $municipios->data;
foreach ($municipios as $item) {
	if ($item->Codigo == $CodigoMunicipio) {
		$cidadeTomador= $item->Nome;
		break;
	}
}

$ufTomador=(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Endereco->Uf;
$email_prestador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->PrestadorServico->Contato->Email;   
if(isset($xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Contato->Email)){
	$email_tomador= (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Contato->Email;   
}else{
	$email_tomador ='Não Informado';
}
$telefone_prestador ='';  
$telefoneTomador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->Contato->Telefone;
$razaoSocialTomador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->RazaoSocial;

if(isset($xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj)){
	$cnpj_tomador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj;
	$cnpj_tomador = mascara('##.###.###/####-##',$cnpj_tomador);
}else{
	$cnpj_tomador = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cpf; 
	$cnpj_tomador = mascara('###.###.###-##',$cnpj_tomador);
}

if(isset($xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->InscricaoMunicipal)){
	$IM_tomador=$xml->ListaNfse->CompNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->InscricaoMunicipal;
}else{
	$IM_tomador='';
}
$valorServicos = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Valores->ValorServicos;
$deducoes=0;
$retencoes_federais=0;
$valor_iss_retido=0;
$base_calculo= (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Valores->BaseCalculo;
$discriminacao = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Discriminacao;
$OutrasInformacoes = (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->OutrasInformacoes;
$codigoTributacao=  (string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->CodigoTributacaoMunicipio;
$ItemListaServico=(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->ItemListaServico;
$valor_liquido=(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Valores->ValorLiquidoNfse;
$OptanteSimplesNacional=(string)$xml->ListaNfse->CompNfse->Nfse->InfNfse->OptanteSimplesNacional;
if(isset($xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Valores->ValorIss)){
	$ValorIss= 'R$ '.number_format($xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Valores->ValorIss, 2, ',', '.');
}else{
	$ValorIss='-';
}
if(isset($xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Valores->Aliquota)){
	$aliquota='R$ '.number_format($xml->ListaNfse->CompNfse->Nfse->InfNfse->Servico->Valores->Aliquota, 2, ',', '.');
}else{
	$aliquota='-';

}

if($OptanteSimplesNacional ==1){
	$OptanteSimplesNacional='Documento emitido por ME ou EPP optante pelo Simples Nacional. Não gera direito a crédito fiscal de IPI.';
}
$RegimeEspecialTributacao='';
if(isset($xml->ListaNfse->CompNfse->Nfse->InfNfse->RegimeEspecialTributacao)){
	if($xml->ListaNfse->CompNfse->Nfse->InfNfse->RegimeEspecialTributacao ==6){
		$RegimeEspecialTributacao='<span class="subTitulo"> Regime Especial de Tributação: </span>ME ou EPP do Simples Nacional';
	}
}

if($ItemListaServico == '13.05'){

	$ItemListaServico='13.05 / Composição gráfica, inclusive confecção de impressos gráficos, fotocomposição, clicheria, zincografia, litografia e fotolitografia, exceto se destinados a posterior operação de comercialização ou industrialização, ainda que incorporados, de qualquer forma, a outra mercadoria que deva ser objeto de posterior circulação, tais como bulas, rótulos, etiquetas, caixas, cartuchos, embalagens e manuais técnicos e de instrução, quando ficarão sujeitos ao ICMS.';
}
}else{
     echo 'ACESSO NEGADO!';
      die();
}
}else{
    echo 'ACESSO NEGADO!';
    die();
    
}
function mascara($mascara,$string){
	$string_mascarada = '';
	$k = 0;
	$i = 0;
	while($i <=(strlen($mascara)-1))
	{
		if($mascara[$i] == '#')
		{
			if(isset($string[$k]))
				$string_mascarada .= $string[$k++];
		}else
		{
			if(isset($mascara[$i]))
				$string_mascarada .= $mascara[$i];
		}
		$i++;
	}
	return $string_mascarada;

}

?>
<link rel="stylesheet" type="text/css" href="css/a1.css">
<link rel="stylesheet" type="text/css" href="css/a2.css">
<link rel="stylesheet" type="text/css" href="css/a3.css">
<link rel="stylesheet" type="text/css" href="css/a4.css">
<link rel="stylesheet" type="text/css" href="css/a5.css">
<div id="moldura">
<?=$div_cancelamento ?>
<a href='https://bhissdigital.pbh.gov.br/nfse/pages/consultaNFS-e_cidadao_creditoIPTU.jsf'
target="_blank"  style="font-size:15px;margin-bottom:5px;" class="noprint">CLIQUE AQUI PARA VERIFICAR AUTENTICIDADE DA NFS-e <?= $numero_nfe ?></a><input class="noprint" id="form:j_id20" name="form:j_id20" onclick="window.location='javascript:self.print()'" value="Imprimir esta Nota Fiscal" style="border: none;" type="image" src="img/bt_imprimir.gif" alt="Imprimir esta Nota Fiscal">
<table id="container" border="0" cellpadding="0" cellspacing="0" width="646">
	<tbody>
		<tr>
			<td>
				<div class="hh1">
					NFS-e - NOTA FISCAL DE SERVIÇOS ELETRÔNICA
				</div>
			</td>
		</tr>
		<tr>
			<td class="bordaInferior">
				<table width="100%" border="0" cellspacing="2" cellpadding="0">
					<tbody>
						<tr class="teste">
							<td width="25%" class="bordaLateral">
								<span class="numeroDestaque">Nº: <?= $numero ?> </span>
							</td>
							<td width="31%" class="bordaLateral">
								<span class="subTitulo">Emitida em:<br> </span>
								<span class="dataEmissao"><?= date('d/m/Y H:i:s', strtotime($dataEmissao)) ?> </span>
							</td>
							<td width="17%" class="bordaLateral">
								<span class="subTitulo">Competência:</span>
								<br><span class="dataEmissao"><?= date('d/m/Y', strtotime($dataEmissao)) ?></span>
							</td>
							<td width="27%">
								<span class="subTitulo">Código de Verificação:<br></span>
								<span class="dataEmissao"><?= $codigoVerificacao ?></span>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table border=" 0" cellpadding="0" cellspacing="0" width="100%">
					<tbody>
						<tr>
							<td valign="top">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tbody>
										<tr>
											<td width="160"><img style="max-width: 200px;" src="<?= $logo ?>" align="middle"></td>
											<td width="491">
												<div class="hh3"><?= $razaoSocialPrestador ?></div>
												<table width="100%" cellspacing="1" class="teste">
													<tbody>
														<tr>
															<td width="52%">
																<span class="cnpjPrincipal" id="j_id">
																	CPF/CNPJ:&nbsp;<?= $cnpjPrestador ?> </span>
																</td>
																<td width="48%">
																	<span class="cnpjPrincipal" id="j_id2">
																		Inscrição Municipal:&nbsp;<?= $inscricaoMunicipalPrestador ?> </span>
																	</td>
																</tr>
																<tr>
																	<td colspan="2"><?= $enderecoPrestador ?></td>
																</tr>
																<tr>
																	<td>
																		<span id="j_id3">Belo Horizonte</span>
																	</td>
																	<td>
																		<span id="j_id5">MG </span>
																	</td>
																</tr>
																<tr>
																	<td>Telefone: &nbsp;<?= $telefone_prestador?></td>
																	<td>Email: &nbsp;<?= $email_prestador?></td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr class="linhaDivisao">
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<div class="box02">
															<table>
																<tbody>
																	<tr>
																		<td colspan="2">
																			<div class="hh2">
																				Tomador do(s) Serviço(s)
																			</div>
																		</td>
																	</tr>
																	<tr class="teste">
																		<td width="52%">
																			<span class="cnpjPrincipal" id="j_id27">
																				CPF/CNPJ:&nbsp;<?= $cnpj_tomador ?></span>
																			</td>
																			<td width="48%">
																				<span class="cnpjPrincipal" id="j_id33">
																					Inscrição Municipal:<?= $IM_tomador ?> </span>
																				</td>
																			</tr>
																			<tr class="teste">
																				<td colspan="2">
																					<span class="cnpjPrincipal"><?= $razaoSocialTomador ?></span>
																				</td>
																			</tr>
																			<tr class="teste">
																				<td colspan="2"><?= $enderecoTomador ?></td>
																			</tr>
																			<tr class="teste">
																				<td>
																					<span id="j_id41"><?= $cidadeTomador ?></span>
																				</td>
																				<td>
																					<span id="j_id44"><?= $ufTomador ?></span>
																				</td>
																			</tr>
																			<tr class="teste">
																				<td>Telefone:&nbsp;<?= $telefoneTomador ?></td>
																				<td>Email:&nbsp;<?= $email_tomador ?></td>
																			</tr>
																		</tbody>
																	</table>
																	<img src="img/bottom_box02.gif" alt="." class="noprint">
																</div>
																<hr class="linhaDivisao">
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<div class="box02">
									<table border="0" cellpadding="4" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td width="100%">
													<div class="hh2">
														Discriminação do(s) Serviço(s)
													</div>
												</td>
											</tr>
											<tr>
												<td class="servicos"><?= $discriminacao ?></td>
											</tr>
										</tbody>
									</table>
									<img src="img/bottom_box02.gif" alt="." class="noprint">
									<hr class="linhaDivisao">
								</div>
								<div class="box04">
									<table border="0" cellpadding="2" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td>
													<span class="subTitulo">Código de Tributação do Município (CTISS)</span>
													<p class="teste"><?= $codigoTributacao ?> </p>
												</td>
											</tr>
										</tbody>
									</table>
									<hr class="linhaDivisao">
								</div>
								<div style="margin: 0px 5px;">
									<p class="teste">
										<span class="subTitulo">Subitem Lista de Serviços LC 116/03 / Descrição:</span>
									</p>
									<p class="teste"><?= $ItemListaServico ?> </p>
									<hr class="linhaDivisao">
								</div>
								<div class="box04">
									<span id="j_id106"> </span>
									<table border="0" cellpadding="2" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td valign="top" width="50%">
													<p class="teste">
														<span class="subTitulo">Cod/Município da incidência do ISSQN:</span>
													</p>
													<p class="teste">3106200 / Belo Horizonte</p>
												</td>
												<td valign="top" width="50%">
													<span class="subTitulo">Natureza da Operação:</span>
													<p class="teste">Tributação no município</p>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
								<span id="form:j_id130">
									<div style="margin: 0px 5px;">
										<span id="j_id106"> </span>
										<span id="form:j_id132"></span>
										<table border="0" cellpadding="4" cellspacing="0" width="100%">
											<tbody>
												<tr>
													<td width="33%" height="25" align="center" valign="middle" class="bordaLateral">
														<p class="teste">
															<?= $RegimeEspecialTributacao ?>

														</p>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</span>
								<hr class="linhaDivisao">
							</td>
						</tr>
						<tr>
							<td>
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tbody>
										<tr>
											<td valign="top" class="bordaInferior">
												<div class="box05">
													<table border="0" cellpadding="4" cellspacing="0" width="100%">
														<tbody>
															<tr>
																<td class="hh2" width="44%">
																	Valor dos serviços:
																</td>
																<td class="hh2" width="56%" align="right">R$ <?= number_format($valorServicos, 2, ',', '.') ?></td>
															</tr>
															<tr>
																<td colspan="2">
																	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableValores">
																		<tbody>
																			<tr>
																				<td width="131" class="bordaInferior">
																					(-) Descontos:
																				</td>
																				<td width="165" align="right" class="bordaInferior">R$ 0,00</td>
																			</tr>
																			<tr>
																				<td class="bordaInferior">
																					(-) Retenções Federais:
																				</td>
																				<td align="right" class="bordaInferior">R$ <?= number_format($retencoes_federais, 2, ',', '.') ?></td>
																			</tr>
																			<tr>
																				<td class="bordaInferior">
																					(-) ISS Retido na Fonte:
																				</td>
																				<td align="right" class="bordaInferior">R$ <?= number_format($valor_iss_retido, 2, ',', '.') ?></</td>
																			</tr>
																			<tr>
																				<td class="valorLiquido">
																					<b>Valor Líquido:</b>
																				</td>
																				<td align="right" class="valorLiquido">
																					<b>R$ <?= number_format($valorServicos, 2, ',', '.') ?></ </b>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<img src="img/bottom_box05.gif" alt="." class="noprint">
												</div>
											</td>
											<td class="bordaInferior">
												<div class="box05">
													<table border="0" cellpadding="4" cellspacing="0" width="100%">
														<tbody>
															<tr>
																<td width="42%" class="hh2">
																	Valor dos serviços:
																</td>
																<td width="58%" align="right" class="hh2">R$ <?= number_format($valorServicos, 2, ',', '.') ?></td>
															</tr>
															<tr>
																<td colspan="2">
																	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="teste">
																		<tbody>
																			<tr>
																				<td width="152" class="bordaInferior">
																					(-) Deduções:
																				</td>
																				<td width="144" align="right" class="bordaInferior">R$ <?= number_format($deducoes, 2, ',', '.') ?></td>
																			</tr>
																			<tr>
																				<td class="bordaInferior">
																					(-) Desconto Incondicionado:
																				</td>
																				<td align="right" class="bordaInferior">R$ 0,00</td>
																			</tr>
																			<tr>
																				<td class="bordaInferior" style="font-size: 12px;">
																					<strong>(=) Base de Cálculo:</strong>
																				</td>
																				<td align="right" class="bordaInferior" style="font-size: 12px;">
																					<strong> <?= number_format($base_calculo, 2, ',', '.') ?></strong>
																				</td>
																			</tr>
																			<tr>
																				<td class="bordaInferior">
																					(x) Alíquota:
																				</td>
																				<td align="right" class="bordaInferior">
																					<b><?= $aliquota ?></b>
																				</td>
																			</tr>
																			<tr>
																				<td class="valorLiquido">
																					<b>(=)Valor do ISS:</b>
																				</td>
																				<td align="right" class="valorLiquido">
																					<b> <?= $ValorIss ?></b>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<img src="img/bottom_box05.gif" alt="." class="noprint">
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td class="bordaInferior" style="padding: 5px;">
								<span class="subTitulo"><?= $OptanteSimplesNacional?></span>
							</td>
						</tr>
						<tr>
							<td class="bordaInferior" style="padding: 5px;">
								<span class="subTitulo">Outras Informações:</span>
								<p class="teste">
									<span style="color: red;"><b><?= $OutrasInformacoes ?>.</b></span>
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<table class="tableNoBorder" border="0" cellpadding="4" cellspacing="0" width="100%">
									<tbody>
										<tr>
											<td width="9%">
												<img src="img/img_brasao_titulo.gif" alt="Brasão Prefeitura">
											</td>
											<td width="82%">
												<span class="subTitulo">Prefeitura de Belo Horizonte - Secretaria Municipal de Fazenda</span>
												<p class="teste">
													Rua Espírito Santo, 605 - 3º andar - Centro - CEP: 30160-919 - Belo Horizonte MG.
													<br>
													Dúvidas: SIGESP
													<br>
												</p>
											</td>
											<td width="9%">
												<img src="img/bh_nota_10.jpg" alt="Brasão Prefeitura">
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
				</div>
			?>
