<?php
	class DataAccess
	{
		protected $connection;
		 
		public function connect()
		{
			$bd = "clinic11_rep";
			$user = "clinic11_fmv";
			$pwd = "serodio";
			$server = "127.0.0.1";
			
			$this->connection = mysql_connect($server, $user, $pwd);
			
			if( $this->connection <0 || mysql_select_db($bd, $this->connection) == false ) 
			{
				die('Erro na ligação à base de dados: '.mysql_error());
			}
			else
			{
				mysql_query("set names 'utf8'");
				mysql_query("set character_set_connection=utf8");
				mysql_query("set character_set_client=utf8");
				mysql_query("set character_set_results=utf8");
			}
		}
		
		public function executarQuery($query)
		{
			$res = mysql_query($query);
			if (!$res)
			{
				die('A query é inválida '.mysql_error());
			}
			else
			{
				return $res;
			}
		}
		
		public function disconnect()
		{
			mysql_close($this->connection);
		}

		private function decryptStringArray ($stringArray, $key = "agsagasgewqgtqwgasg")
		{
			$s = unserialize(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(strtr($stringArray, '-_,', '+/=')), MCRYPT_MODE_CBC, md5(md5($key))), "\0"));
			return $s;
		}
		
		function setGET($params,$key = "agsagasgewqgtqwgasg") 
		{
			$params = $this->decryptStringArray($params,$key);
			$param_pairs = explode('&',$params);
			foreach($param_pairs as $pair)
			{
			   $split_pair = explode('=',$pair);
			   $_GET[$split_pair[0]] = $split_pair[1];
			}
		}
		
		private function encryptStringArray ($stringArray, $key = "agsagasgewqgtqwgasg")
		{
			$s = strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), serialize($stringArray), MCRYPT_MODE_CBC, md5(md5($key)))), '+/=', '-_,');
			return $s;
		}
	 
		function prepareUrl($url, $key = "agsagasgewqgtqwgasg")
		{
			$url = explode("?",$url,2);
			if(sizeof($url) <= 1)
			   return $url;
			else
			   return $url[0]."?params=".$this->encryptStringArray($url[1],$key);
		}
		
		function login()
		{
			if(isset($_POST['submit']))
			{
				$Email = $_POST['Email'];
				$Password = $_POST['Password'];
				$this->connect();
				if ($Email == "" || $Password == "")
				{
					$url = $this->prepareUrl("index.php", "ec");
					echo"<script>alert('Existem campos por preencher!')</script>";
					echo"<script>window.location='$url'</script>";
				}
				else
				{
					$Email = stripslashes($Email);
					$Password = stripslashes($Password);
					$Email = mysql_real_escape_string($Email);
					$Password = mysql_real_escape_string($Password);
					$Password = md5($Password);

					$query = "select * from utilizadores where Email = '".$Email."' and Password = '".$Password."'";
					$res = $this->executarQuery($query);

					if ( mysql_num_rows($res) > 0 )
					{
						$row = mysql_fetch_row($res);

						$_SESSION['ID'] = $row[0];
						$_SESSION['Email'] = $row[1];
						$_SESSION['PNome'] = $row[2];
						$_SESSION['UNome'] = $row[3];
						$_SESSION['Curso'] = $row[4];
						$_SESSION['Ciclo'] = $row[5];
						$_SESSION['DataCriacao'] = $row[7];
						$_SESSION['IDTipo'] = $row[8];
						
						$url = $this->prepareUrl("index.php?l=1", "ec");
						
						echo "<script>alert('Bem vindo ".$_SESSION['PNome']." ". $_SESSION['UNome']."')</script>";
						echo"<script>window.location='$url'</script>";
					}
					else
					{
						echo"<script>alert('E-mail ou palavra-passe incorretos!')</script>";
						echo "<script>window.location='index.php'</script>";
					}
				}
				$this->disconnect();
			}
			else
			{
				$url = $this->prepareUrl("index.php?l=4", "ec");
				echo "<hr style='height:10%; visibility:hidden;'/>
				<div class='large-3 medium-3 small-3 large-centered columns'>
						<div class='panel'>
							<form action='' method='POST'>
								<p><input type='text' name='Email' placeholder='E-mail' autofocus='autofocus' required /></p>
								<p><input type='password' name='Password' placeholder='Password' required /></p>
								<center><input type='submit' name='submit' class='button' value='Entrar'/></center>
							</form>
						</div>
					</div>
					<center><a href='$url'>Registe-se aqui!</a></center>
					<hr style='height:10%; visibility:hidden;'/>
					";
			}
		}
		
		public function logout()
		{
			session_destroy();
			echo"<script type='text/javascript'>window.location = 'index.php';</script>";
		}
		
		public function header()
		{
			$url1 = $this->prepareUrl("index.php?l=1", "ec");
			$url2 = $this->prepareUrl("index.php?l=2", "ec");
			$url3 = $this->prepareUrl("index.php?l=3", "ec");
			$url4 = $this->prepareUrl("index.php?l=5", "ec");
			$url5 = $this->prepareUrl("index.php?l=10", "ec");
			echo "
			<div class='row'>
				<div class='large-12 columns'>
					<center><img src='img/banner.png'></center>
				</div>
			</div>
			";

			if(isset($_SESSION['ID']))
			{
				echo"
				<ul class='breadcrumbs'>
					<li><a href='$url1'>Início</a></li>
					<li><a href='$url2'>Pesquisar</a></li>
					<li><a href='$url5'>Dados Pessoais</a></li>
					";
					if ($_SESSION['IDTipo'] == 2)
					{
						echo "
						<li><a href='$url3'>Enviar</a></li>";
					}
					
					echo "
					<li><a href='$url4'>Sair (".$_SESSION['PNome']." ".$_SESSION['UNome'].")</a></li>
				</ul>
				";
			}
			else
			{
				echo"
				<ul class='breadcrumbs'>
					<li><a href='index.php'>Início</a></li>
				</ul>";
			}
		}
		
		function entrada()
		{
			echo"<script>
				function confirmation()
				{
					var answer = confirm('Tem a certeza que deseja eliminar o registo?');
					if (answer)
					{
						alert('Registo eliminado');
						sleep(2000);
						return true;
					}
					else
					{
						if (window.event)
						{
							window.event.returnValue=false; //internet explorer
						}
						else
						{
							return false;
						}
					}
				}
		    </script>"; 
			
			$this->connect();
			
			$query = "select *,
					envios.ID as IDE,
					envios.DataCriacao as DC,
					envios.NomeRelatorio as NOME,
					envios.NomeApresentacao as NOMEA,
					envios.AnoLetivo as ANOLETIVO,
					enviosutilizadores.IDUtilizador as IDUE,
					enviosutilizadores.IDEnvio,
					utilizadores.ID as IDU,
					utilizadores.PNome as PNOME,
					utilizadores.UNome as UNOME,
					utilizadores.Curso as CUR,
					utilizadores.Ciclo as CIC
				from
					envios
					inner join enviosutilizadores
					inner join utilizadores
				where
					envios.ID = enviosutilizadores.IDEnvio and
					utilizadores.ID = enviosutilizadores.IDUtilizador order by envios.ID desc LIMIT 10 ";
			
			$res = $this->executarQuery($query);
			
			if(mysql_num_rows($res) != "")
			{
				
					echo "
					<hr style='height:5%; visibility:hidden;'/>
					<div class='large-10 medium-10 small-10 large-centered columns'>
						<div align='center'>
							<table>
								<h3>Últimos 10 envios</h3>
								<tr>
									<th>
										Relatório
									</th>
									";
									if($_SESSION['IDTipo'] == 2)
									{
										echo"
										<th>
											
										</th>";
									}
									echo"
									<th>
										Apresentação
									</th>
									";
									if($_SESSION['IDTipo'] == 2)
									{
										echo"
										<th>
											
										</th>
										";
									}
									echo"
									<th width='200'>
										Aluno
									</th>
									<th align='center'>
										Curso
									</th>
									<th align='center'>
										Ano Letivo
									</th>
									<th width='110' align='center'>
										Data de Envio
									</th>
									";
									if($_SESSION['IDTipo'] == 2)
									{
										echo"
										<th>
											
										</th>
										";
									}
									echo"
								</tr>
								";
				while($row = mysql_fetch_assoc($res))
				{
					$url = $this->prepareUrl('index.php?l=6&d=1&ID='.$row['IDE'].'', "ec");
					$url1 = $this->prepareUrl('index.php?l=6&d=2&ID='.$row['IDE'].'', "ec");
					$url2 = $this->prepareUrl('index.php?l=7&ID='.$row['IDE'].'', "ec");
					$url3 = $this->prepareUrl('index.php?l=8&ID='.$row['IDE'].'&loc=1', "ec");
					$url4 = $this->prepareUrl('index.php?l=9&ID='.$row['IDE'].'', "ec");
					$url5 = "relatorios/".$row['IDE'].".".$this->getTipoPeloNome($row['NOME']);
					$url6 = "apresentacoes/".$row['IDE'].".".$this->getTipoPeloNome($row['NOMEA']);
					//echo "<script>alert('$url')</script>";
					echo"
									<tr>
										<td>
										<a href='$url5' title='Download'><img src='img/download.png'  width='20' height='20'>"." ".$row['NOME']."</a>
										</td>
										";
										if($_SESSION['IDTipo'] == 2)
										{
											echo"
											<td>
												<a href='$url4'>
													<img src='img/editar.jpg'  width='30' height='30' title='Editar'>
												</a>
											</td>";
										}
											if($row['NOMEA'] != "")
											{
												echo "<td>
												<a href='$url6' title='Download'><img src='img/download.png'  width='20' height='20'>"." ".$row['NOMEA']."</a>";
												if($_SESSION['IDTipo'] == 2)
												{
													echo"
													<td>
														<a href='$url2'>
															<img src='img/editar.jpg'  width='30' height='30' title='Editar'>
														</a>
													</td>";
												}
											}
											else
											{
												if ($_SESSION['IDTipo'] == 2 && $row['NOMEA'] == "")
												{
													echo "<td align='center'><a href='$url2'>Enviar</a>
													<td>
													</td>";
												}
												else
												{
													echo "<td align='center'>Não enviada
													</td>
													";
												}
											}
											echo "
										</td>
										<td align='center'>
											".$row['PNOME']." ".$row['UNOME']."
										</td>
										<td align='center'>
											".$row['CUR']."
										</td>
										<td align='center'>
											".$row['ANOLETIVO']."
										</td>
										<td align='center'>
											".date('d-M-Y', strtotime($row['DC']))."
										</td>";
										if($_SESSION['IDTipo'] == 2)
										{
											echo"
											<td width='35'>
												<a href='$url3' onclick='return confirmation()'>
													<img src='img/eliminar.jpg'  width='30' height='30' title='Eliminar'>
												</a>
											</td>";
										}
										echo"
									</tr>";
				}
				echo"
							</table>
						</div>
					</div>
					<hr style='height:10%; visibility:hidden;'/>";
			}
			else
			{
				echo "
					<hr style='height:10%; visibility:hidden;'/>
					<div class='large-10 medium-10 small-10 large-centered columns'>
						<div align='center'>
							<table>
								<tr>
									<td align='center'>
										Relatório
									</td>
									<td align='center'>
										Apresentação
									</td>
									<td align='center'>
										Autor
									</td>
									<td align='center'>
										Curso
									</td>
									<td align='center'>
										Ciclo
									</td>
									<td align='center'>
										Data de Envio
									</td>
								</tr>
								<tr>
									<td align='center' colspan='6'>
										Não existem submissões
									</td>
								</tr>
								</table>
						</div>
					</div>
					<hr style='height:10%; visibility:hidden;'/>
					";
			}
		}
		
		function download()
		{
			$IDE = $_GET['ID'];
			$D = $_GET['d'];
			
			$this->connect();
			
			if($D == 1)
			{
				$query = "select *,
					envios.ID,
					envios.NomeRelatorio as NOME,
					envios.TipoFicheiroRelatorio as TIPO,
					envios.ConteudoRelatorio as CONTEUDO,
					envios.TamanhoFicheiroRelatorio as TAMANHO
				from
					envios
				where
					envios.ID = ".$IDE;
			}
			else if($D == 2)
			{
				$query = "select *,
					envios.ID,
					envios.NomeApresentacao as NOME,
					envios.TipoFicheiroApresentacao as TIPO,
					envios.ConteudoApresentacao as CONTEUDO,
					envios.TamanhoFicheiroApresentacao as TAMANHO
				from
					envios
				where
					envios.ID = ".$IDE;
			}
			
			$res = $this->executarQuery($query);
			$row = mysql_fetch_assoc($res);
						
			$nome = $row['NOME'];
			$tamanho = $row['TAMANHO'];
			$tipo = $row['TIPO'];
			$conteudo = $row['CONTEUDO'];
			
			
			echo "<script>alert('$nome')</script>";			
			echo "<script>alert('$tamanho')</script>";			
			echo "<script>alert('$tipo')</script>";			
			//echo "<script>alert('$conteudo')</script>";			
			
		
			
			header("Content-length: ".$tamanho."");
			header("Content-type: ".$tipo."");
			header('Content-Disposition: attachment; filename="'.$nome.'"');
			echo $conteudo;
			//readfile($nome);
			//ob_clean();
			//flush();
			//readfile($nome);
			//echo $conteudo; 
			
			$this->disconnect();
		}
		
		function eliminar()
		{
			$this->connect();
			
			$IDE = $_GET['ID'];
			$loc = $_GET['loc'];
			
			$query = "DELETE FROM enviosutilizadores WHERE IDEnvio = '$IDE'";
			$this->executarQuery($query);
			
			$query1 = "DELETE FROM envios WHERE ID = '$IDE'";
			$this->executarQuery($query1);
			
			$this->disconnect();
			
			if($loc == 1)
			{
				$url = $this->prepareUrl("index.php?l=1", "ec");
				echo"<script>window.location='$url'</script>";
			}
			else
			{
				$pesq = $_GET['pesq'];
				$url = $this->prepareUrl("index.php?l=2&pesq=".$pesq."", "ec");
				echo"<script>window.location='$url'</script>";
			}
		}
		
		function pesquisa()
		{
			echo"<script>
				function confirmation()
				{
					var answer = confirm('Tem a certeza que deseja eliminar o registo?');
					if (answer)
					{
						alert('Registo eliminado');
						sleep(2000);
						return true;
					}
					else
					{
						if (window.event)
						{
							window.event.returnValue=false; //internet explorer
						}
						else
						{
							return false;
						}
					}
				}
		    </script>";
		
			if(isset($_POST['submit']) || isset($_POST['pesq']))
			{
				if(!isset($_POST['pesq']))
				{
					$pesq = mysql_real_escape_string($_POST['pesq']);
				}
				else
				{
					$pesq = $_POST['pesq'];
				}
				if($pesq != "")
				{
					$this->connect();
					$query = "select *,
						envios.ID as IDE,
						envios.DataCriacao as DC,
						envios.NomeRelatorio as NOME,
						envios.NomeApresentacao as NOMEA,
						envios.AnoLetivo as ANOLETIVO,
						enviosutilizadores.IDUtilizador as IDUE,
						enviosutilizadores.IDEnvio,
						utilizadores.ID as IDU,
						utilizadores.PNome as PNOME,
						utilizadores.UNome as UNOME,
						utilizadores.Curso as CUR,
						utilizadores.Ciclo as CIC
					from
						envios
						inner join enviosutilizadores
						inner join utilizadores
					where
						envios.ID = enviosutilizadores.IDEnvio and
						utilizadores.ID = enviosutilizadores.IDUtilizador and
						(envios.AnoLetivo = '$pesq' OR
						CONCAT(utilizadores.PNome,' ',utilizadores.UNome) LIKE '%$pesq%' OR
						utilizadores.Curso = '$pesq')
						";
				}
				else
				{
					$this->connect();
					$query = "select *,
						envios.ID as IDE,
						envios.DataCriacao as DC,
						envios.NomeRelatorio as NOME,
						envios.NomeApresentacao as NOMEA,
						envios.AnoLetivo as ANOLETIVO,
						enviosutilizadores.IDUtilizador as IDUE,
						enviosutilizadores.IDEnvio,
						utilizadores.ID as IDU,
						utilizadores.PNome as PNOME,
						utilizadores.UNome as UNOME,
						utilizadores.Curso as CUR,
						utilizadores.Ciclo as CIC
					from
						envios
						inner join enviosutilizadores
						inner join utilizadores
					where
						envios.ID = enviosutilizadores.IDEnvio and
						utilizadores.ID = enviosutilizadores.IDUtilizador
						";
				}
				
				$res = $this->executarQuery($query);
				
				if(mysql_num_rows($res) != "")
				{
						echo "
						<hr style='height:5%; visibility:hidden;'/>
						<div class='large-10 medium-10 small-10 large-centered columns'>
							<div align='center'>
								<table>
										";
										if($pesq != "")
										{
											echo "<h3>Pesquisa por '$pesq'</h3>";
										}
										else
										{
											echo "<h3>Pesquisa</h3>";
										}
										echo "
										<tr>
											<th>
												Relatório
											</th>";
											if($_SESSION['IDTipo'] == 2)
											{
												echo"
												<th>
													
												</th>";
											}
											echo"
											<th>
												Apresentação
											</th>";
											if($_SESSION['IDTipo'] == 2)
											{
												echo"
												<th>
													
												</th>";
											}
											echo"
											<th width='200'>
												Aluno
											</th>
											<th align='center'>
												Curso
											</th>
											<th align='center'>
												Ano Letivo
											</th>
											<th align='center'>
												Data de Envio
											</th>
										</tr>
									";
					while($row = mysql_fetch_assoc($res))
					{
						$url = $this->prepareUrl('index.php?l=6&d=1&ID='.$row['IDE'].'', "ec");
						$url1 = $this->prepareUrl('index.php?l=6&d=2&ID='.$row['IDE'].'', "ec");
						$url2 = $this->prepareUrl('index.php?l=7&ID='.$row['IDE'].'', "ec");
						$url3 = $this->prepareUrl('index.php?l=8&ID='.$row['IDE'].'&loc=2&pesq='.$pesq.'', "ec");
						$url4 = $this->prepareUrl('index.php?l=9&ID='.$row['IDE'].'', "ec");
						$url5 = "relatorios/".$row['IDE'].".".$this->getTipoPeloNome($row['NOME']);
					    $url6 = "apresentacoes/".$row['IDE'].".".$this->getTipoPeloNome($row['NOMEA']);
						echo"
										<tr>
											<td>
											<a href='$url5'><img src='img/download.png'  width='20' height='20'>"." ".$row['NOME']."</a>
											</td>
												";
												if($_SESSION['IDTipo'] == 2)
												{
													echo"
													<td>
														<a href='$url4'>
															<img src='img/editar.jpg'  width='30' height='30' title='Editar'>
														</a>
													</td>";
												}
												if($row['NOMEA'] != "")
												{
													echo "<td>
													<a href='$url6' title='Download'><img src='img/download.png'  width='20' height='20'>"." ".$row['NOMEA']."</a>";
													if($_SESSION['IDTipo'] == 2)
													{
														echo"
														<td>
															<a href='$url2'>
																<img src='img/editar.jpg'  width='30' height='30' title='Editar'>
															</a>
														</td>";
													}
												}
												else
												{
													if ($_SESSION['IDTipo'] == 2 && $row['NOMEA'] == "")
													{
														echo "<td align='center'><a href='$url2'>Enviar</a>
														<td>
														</td>";
													}
													else
													{
														echo "<td align='center'>Não enviada
														</td>";
													}
												}
												echo "
											</td>
											<td align='center'>
												".$row['PNOME']." ".$row['UNOME']."
											</td>
											<td align='center'>
												".$row['CUR']."
											</td>
											<td align='center'>
												".$row['ANOLETIVO']."
											</td>
											<td width='120' align='center'>
												".date('d-M-Y', strtotime($row['DC']))."
											</td>";
											if($_SESSION['IDTipo'] == 2)
											{
												echo"
												<td width='35'>													
													<a href='$url3' onclick='return confirmation()'>
														<img src='img/eliminar.jpg'  width='30' height='30' title='Eliminar'>
													</a>
												</td>";
											}
											echo"
										</tr>
						";
					}
					echo"
								</table>
							</div>
						</div>
						<hr style='height:10%; visibility:hidden;'/>";
				}
				else
				{
					if($pesq != "")
					{
						echo"<script>alert('Não foram encontrados resultados na pesquisa por: $pesq')</script>";
					}
					else
					{
						echo"<script>alert('Não foram encontrados resultados')</script>";
					}
					$url = $this->prepareUrl("index.php?l=2", "ec");
					echo"<script>window.location='$url'</script>";
				}
			}
			else
			{
				echo "
				<hr style='height:15%; visibility:hidden;'/>
				<div class='large-10 medium-10 small-10 large-centered columns'>
					<div align='center'>
						<table width='400px'>
							<form method='post' action=''>
								<tr>
									<td>";
										if ($_SESSION['IDTipo'] == 2)
										{
											echo "<input type='text' name='pesq' id='pesq' placeholder='ex: 13/14 / Manuel Abrantes / GPSI (qualquer dos 3)'>";
										}
										else
										{
											echo "<input type='text' name='pesq' id='pesq' required placeholder='ex: 13/14 / Manuel Abrantes / GPSI (qualquer dos 3)'>";
										}
										echo "
									</td>
								</tr>
								<tr>
									<td colspan='2' align='center'>
										<input type='submit' name='submit' class='button' value='Pesquisar'/>
									</td>
								</tr>                   
							</form>
						</table>
					</div>
				</div>
				<hr style='height:15%; visibility:hidden;'/>
			";
			}
		}
		
		function getTipo($tipo){
			$x = explode('/', $tipo);
			return $x[1];
		}
		
		function getTipoPeloNome($nome){
			$x = explode('.', $nome);
			return $x[1];
		}
		
		function envio()
		{
			if(isset($_POST['submit']))
			{				
				if(isset($_POST['submit']))
				{
					ini_set('memory_limit', '-1');
					//relatorio
					$nome = $_FILES['relatorio']['name']; 
					//$nome = mysql_real_escape_string($_FILES['relatorio']['name']); 
					$tmp_nome = $_FILES['relatorio']['tmp_name']; 
					$tipo = $_FILES['relatorio']['type']; 
					$tamanho = $_FILES['relatorio']['size']; 
					//$anoletivo = mysql_real_escape_string($_POST['anoletivo']);
					$anoletivo = $_POST['anoletivo'];
					$aluno = $_POST['aluno'];
					
			//		echo "<script>alert('$nome')</script>";
			//		echo "<script>alert('$tmp_nome')</script>";
			//		echo "<script>alert('".$this->getTipo($tipo)."')</script>";
					
					$allowed_extensions = array(
					'doc', 'dot', 'docx', 'docm', 'dotx',
					'dotm', 'pdf', 'potx', 'potm', 'zip');
					
					//if (!in_array(pathinfo($nome, PATHINFO_EXTENSION), $allowed_extensions))
					if (!in_array($this->getTipo($tipo), $allowed_extensions))
					{
						$url = $this->prepareUrl("index.php?l=3", "ec");
						echo"<script>alert('O formato do ficheiro não é suportado')</script>";
						echo"<script>window.location='$url'</script>";
						die();
					}
					
					if($tamanho>'15728640')
					{ 
						echo"<script>alert('O ficheiro não pode exceder os 15mb')</script>";
						die();
						
						$url = $this->prepareUrl("index.php?l=3", "ec");
						echo"<script>window.location='$url'</script>";
					}
					
					if(!get_magic_quotes_gpc())
					{ 
						$nome = addslashes($nome); 
					}
					
					$extrair = fopen($tmp_nome, 'r'); 
					$conteudo = fread($extrair, $tamanho); 
					$conteudo = addslashes($conteudo); 
					fclose($extrair);
					
					$this->connect();

					//$query = "INSERT INTO envios (DataCriacao, NomeRelatorio, TipoFicheiroRelatorio, TamanhoFicheiroRelatorio, ConteudoRelatorio, AnoLetivo) VALUES (NOW(), '$nome', '$tipo', '$tamanho', '$conteudo', '$anoletivo')"; 
					$query = "INSERT INTO envios (DataCriacao, NomeRelatorio, TipoFicheiroRelatorio, TamanhoFicheiroRelatorio, AnoLetivo) VALUES (NOW(), '$nome', '$tipo', '$tamanho', '$anoletivo')"; 
					$this->executarQuery($query);
				
					$ide = mysql_insert_id();
					//echo "<script>alert('$nome')</script>";
					$tipo = $this->getTipoPeloNome($nome);
					//echo "<script>alert('$nome')</script>";
					$nome = "relatorios/".$ide.".".$tipo;
					//echo "<script>alert('$nome')</script>";
					move_uploaded_file($tmp_nome, $nome);	
					$query1 = "INSERT INTO enviosutilizadores(IDUtilizador, IDEnvio) VALUES ('".$aluno."', '$ide')";
					$this->executarQuery($query1);
					
					if($_FILES['apresentacao']['name'] != "")
					{
						//apresentacao
						$nome = mysql_real_escape_string($_FILES['apresentacao']['name']); 
						$tmp_nome = $_FILES['apresentacao']['tmp_name']; 
						$tipo = $_FILES['apresentacao']['type']; 
						$tamanho = $_FILES['apresentacao']['size'];
						$anoletivo = mysql_real_escape_string($_POST['anoletivo']);
						
						$allowed_extensions = array(
						'ppt', 'pps', 'pot', 'pptx', 'pptm',
						'ppsx', 'ppsm', 'potx', 'potm', 'zip');
						
						if (!in_array(pathinfo($nome, PATHINFO_EXTENSION), $allowed_extensions))
						{
							$url = $this->prepareUrl("index.php?l=3", "ec");
							echo"<script>alert('O formato do ficheiro não é suportado')</script>";
							echo"<script>window.location='$url'</script>";
							die();
						}
						
						if($tamanho>'157286400')
						{ 
						echo"<script>alert('O ficheiro não pode exceder os 150mb')</script>";
						die();
						
						$url = $this->prepareUrl("index.php?l=3", "ec");
						echo"<script>window.location='$url'</script>";
						}
						
						if(!get_magic_quotes_gpc())
						{ 
							$nome = addslashes($nome); 
						}
						
						$extrair = fopen($tmp_nome, 'r'); 
						$conteudo = fread($extrair, $tamanho); 
						$conteudo = addslashes($conteudo); 
						fclose($extrair);
						
						//$query = "UPDATE envios set NomeApresentacao = '$nome', TipoFicheiroApresentacao = '$tipo', TamanhoFicheiroApresentacao = '$tamanho', ConteudoApresentacao = '$conteudo' WHERE ID = '$ide'"; 
						$query = "UPDATE envios set NomeApresentacao = '$nome', TipoFicheiroApresentacao = '$tipo', TamanhoFicheiroApresentacao = '$tamanho' WHERE ID = '$ide'"; 
						$this->executarQuery($query);
						$nome = "apresentacoes/".$ide.".".$this->getTipoPeloNome($nome);
						move_uploaded_file($tmp_nome, $nome);
						
					}
					$this->disconnect();
					
					echo"<script>alert('Ficheiro(s) enviado')</script>";
					$url = $this->prepareUrl("index.php?l=1", "ec");
					echo"<script>window.location='$url'</script>";
				}
			}
			else
			{
				echo"
				<hr style='height:15%; visibility:hidden;'/>
					<input type='hidden' id='refreshed' value='no'>
					<script type='text/javascript'>
					onload=function(){
					var e=document.getElementById('refreshed');
					if(e.value=='no')e.value='yes';
					else{e.value='no';location.reload();}
					}
					</script>
					<div class='large-10 medium-10 small-10 large-centered columns'>
						<div align='center'>
							<form enctype='multipart/form-data' action='' method='POST' name='upload'>
								<table width='450px'>
								<tr>
									<td valign='top'>
										Relatório
									</td>
									<td>
										<input type='file' name='relatorio' id='relatorio' required/>
									</td>
								<tr
								<tr>
									<td valign='top'>
										Apresentação
									</td>
									<td>
										<input type='file' name='apresentacao' id='apresentacao'/>
									</td>
								<tr>
								<tr>
									<td valign='middle'>
										Ano Letivo
									</td>
									<td>
										<input type='text' name='anoletivo' id='anoletivo' required placeholder='13/14'/>
									</td>
								</tr>
								<tr>
								<td>
									Aluno
								</td>
								<td>";
									$this->connect();

									$query = "select * from utilizadores where IDTipo = 1 order by PNome";
									$res = $this->executarQuery($query);
									echo "<select name='aluno'>";
									while ($row = mysql_fetch_assoc($res))
									{
										echo"<option value=".$row['ID'].">
											".$row['PNome']." ".$row['UNome']."
										</option>";
									}
									echo "</select>";
								echo "</td>
							</tr>
								<tr>
									<td align='center' colspan='2'>
										<input type='submit' name='submit' class='button' value='Enviar'/>
									</td>
								</tr>
								</table>
							</form>
						</div>
					</div>
					<hr style='height:10%; visibility:hidden;'/>
			";
			}
		}
		
		function envioapr()
		{
			$IDE = $_GET['ID'];
			
			if(isset($_POST['submit']))
			{
				$this->connect();
				
				$nome = mysql_real_escape_string($_FILES['apresentacao']['name']); 
				$tmp_nome = $_FILES['apresentacao']['tmp_name']; 
				$tipo = $_FILES['apresentacao']['type']; 
				$tamanho = $_FILES['apresentacao']['size'];
				
				$allowed_extensions = array(
				'ppt', 'pps', 'pot', 'pptx', 'pptm',
				'ppsx', 'ppsm', 'potx', 'potm', 'zip');
				
				if (!in_array(pathinfo($nome, PATHINFO_EXTENSION), $allowed_extensions))
				{
					$url = $this->prepareUrl("index.php?l=7&ID=$IDE", "ec");
					echo"<script>alert('O formato do ficheiro não é suportado')</script>";
					echo"<script>window.location='$url'</script>";
					die();
				}
				
				if($tamanho>'157286400')
				{ 
				echo"<script>alert('O ficheiro não pode exceder os 150mb')</script>";
				die();
				
				$url = $this->prepareUrl("index.php?l=7&ID=$IDE", "ec");
				echo"<script>window.location='$url'</script>";
				}
				
				if(!get_magic_quotes_gpc())
				{ 
					$nome = addslashes($nome); 
				}
				
				$extrair = fopen($tmp_nome, 'r'); 
				$conteudo = fread($extrair, $tamanho); 
				$conteudo = addslashes($conteudo); 
				fclose($extrair);
				
				//$query = "UPDATE envios set NomeApresentacao = '$nome', TipoFicheiroApresentacao = '$tipo', TamanhoFicheiroApresentacao = '$tamanho', ConteudoApresentacao = '$conteudo' WHERE ID = '$IDE'"; 
				$query = "UPDATE envios set NomeApresentacao = '$nome', TipoFicheiroApresentacao = '$tipo', TamanhoFicheiroApresentacao = '$tamanho' WHERE ID = '$IDE'"; 
			
				$this->executarQuery($query);
				
				$nome = "apresentacoes/".$IDE.".".$this->getTipoPeloNome($nome);
				move_uploaded_file($tmp_nome, $nome);
				
				$this->disconnect();
				
				echo"<script>alert('Ficheiro(s) enviado')</script>";
				$url = $this->prepareUrl("index.php?l=1", "ec");
				echo"<script>window.location='$url'</script>";
			}
			else
			{
				echo"
				<hr style='height:15%; visibility:hidden;'/>
					<input type='hidden' id='refreshed' value='no'>
					<script type='text/javascript'>
					onload=function(){
					var e=document.getElementById('refreshed');
					if(e.value=='no')e.value='yes';
					else{e.value='no';location.reload();}
					}
					</script>
					<div class='large-10 medium-10 small-10 large-centered columns'>
						<div align='center'>
							<form enctype='multipart/form-data' action='' method='POST' name='upload'>
								<table width='450px'>
								<tr>
									<td valign='top'>
										Apresentação
									</td>
									<td>
										<input type='file' name='apresentacao' id='apresentacao'/>
									</td>
								</tr>
								<tr>
									<td align='center' colspan='2'>
										<input type='submit' name='submit' class='button' value='Enviar'/>
									</td>
								</tr>
								</table>
							</form>
						</div>
					</div>
					<hr style='height:10%; visibility:hidden;'/>
				";
			}
		}
		
		function enviorel()
		{
			$IDE = $_GET['ID'];
			
			if(isset($_POST['submit']))
			{
				$this->connect();
				
				$nome = mysql_real_escape_string($_FILES['relatorio']['name']); 
				$tmp_nome = $_FILES['relatorio']['tmp_name']; 
				$tipo = $_FILES['relatorio']['type']; 
				$tamanho = $_FILES['relatorio']['size'];
				
				$allowed_extensions = array(
				'doc', 'dot', 'docx', 'docm', 'dotx',
				'dotm', 'pdf', 'potx', 'potm', 'zip');
				
				if (!in_array(pathinfo($nome, PATHINFO_EXTENSION), $allowed_extensions))
				{
					$url = $this->prepareUrl("index.php?l=9&ID=$IDE", "ec");
					echo"<script>alert('O formato do ficheiro não é suportado')</script>";
					echo"<script>window.location='$url'</script>";
					die();
				}
				
				if($tamanho>'157286400')
				{ 
				echo"<script>alert('O ficheiro não pode exceder os 15mb')</script>";
				die();
				
				$url = $this->prepareUrl("index.php?l=9&ID=$IDE", "ec");
				echo"<script>window.location='$url'</script>";
				}
				
				if(!get_magic_quotes_gpc())
				{ 
					$nome = addslashes($nome); 
				}
				
				$extrair = fopen($tmp_nome, 'r'); 
				$conteudo = fread($extrair, $tamanho); 
				$conteudo = addslashes($conteudo); 
				fclose($extrair);
				
				$query = "UPDATE envios set NomeRelatorio = '$nome', TipoFicheiroRelatorio = '$tipo', TamanhoFicheiroRelatorio = '$tamanho' WHERE ID = '$IDE'"; 
				$this->executarQuery($query);
				
				$tipo = $this->getTipoPeloNome($nome);
				$nome = "relatorios/".$IDE.".".$tipo;
				move_uploaded_file($tmp_nome, $nome);	
					
				$this->disconnect();
				
				echo"<script>alert('Ficheiro(s) enviado')</script>";
				$url = $this->prepareUrl("index.php?l=1", "ec");
				echo"<script>window.location='$url'</script>";
			}
			else
			{
				echo"
				<hr style='height:15%; visibility:hidden;'/>
					<input type='hidden' id='refreshed' value='no'>
					<script type='text/javascript'>
					onload=function(){
					var e=document.getElementById('refreshed');
					if(e.value=='no')e.value='yes';
					else{e.value='no';location.reload();}
					}
					</script>
					<div class='large-10 medium-10 small-10 large-centered columns'>
						<div align='center'>
							<form enctype='multipart/form-data' action='' method='POST' name='upload'>
								<table width='450px'>
								<tr>
									<td valign='top'>
										Relatório
									</td>
									<td>
										<input type='file' name='relatorio' id='relatorio'/>
									</td>
								</tr>
								<tr>
									<td align='center' colspan='2'>
										<input type='submit' name='submit' class='button' value='Enviar'/>
									</td>
								</tr>
								</table>
							</form>
						</div>
					</div>
					<hr style='height:10%; visibility:hidden;'/>
				";
			}
		}
		
		function footer()
		{
			echo "
			<footer class='row'>
			<hr>
				<div class='large-12 columns'>
					<div class='row'>
						<div class='twelve columns' align='center'>
							<a href='http://www.epbjc.pt/barreiro' target='_blank'><p>Escola Profissional Bento de Jesus Caraça</p></a>
						</div>
						</div>
					</div>
				</div>
			</footer>";
		}
		
		function registro()
		{
			if(isset($_POST['submit']))
			{
				$pnome=$_POST['pnome'];
				$unome=$_POST['unome']; 
				$email=$_POST['email']; 
				$curso=$_POST['curso']; 
				$ciclo=$_POST['ciclo']; 
				$password1=$_POST['pw1']; 
				$password2=$_POST['pw2'];
			//	echo "<script>alert('$pnome')</script>";
			//	echo "<script>alert('$unome')</script>";
				$this->connect();
				
				$query = "select * from utilizadores where Email = '$email'";
				$res = $this->executarQuery($query);
				
				if (mysql_num_rows($res) == 0)
				{
					if ($password1 != $password2)
					{	
						$url = $this->prepareUrl("index.php?l=4", "ec");
						echo "<script>alert('As passwords não são iguais!')</script>";
						echo "<script>window.location='$url'</script>";
					}
					else if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $email))
					{
						$url = $this->prepareUrl("index.php?l=4", "ec");
						echo "<script>alert('O seu e-mail tem um formato inválido!')</script>";
						echo "<script>window.location='$url'</script>";
					}
					/*else 
					if (!preg_match("/^[a-zA-Z ]*$/", $pnome))
					{
						$url = $this->prepareUrl("index.php?l=4", "ec");
						echo "<script>alert('Os nomes só podem conter letras e espaços!')</script>";
						echo "<script>window.location='$url'</script>";
					}
					else if (!preg_match("/^[a-zA-Z ]*$/", $unome))
					{
						$url = $this->prepareUrl("index.php?l=4", "ec");
						echo "<script>alert('Os nomes só pode conter letras e espaços!')</script>";
						echo "<script>window.location='$url'</script>";
					}*/
					
					else if($password1 == $password2 && $password1 != "" && $password2 != "")
					{
						$pw=md5($password1);
						$query = "INSERT INTO utilizadores(Email, PNome, UNome, Curso, Ciclo, Password, DataCriacao, IDTipo) VALUES ('$email', '$pnome', '$unome', '$curso', '$ciclo', '$pw', NOW(), '1')";
						$this->executarQuery($query);
						echo "<script>alert('Registo efetuado')</script>";
						echo "<script>window.location.href='index.php'</script>";
					}
				}
				else
				{
					$url = $this->prepareUrl("index.php?l=4", "ec");
					echo"<script>alert('Já existe um utilizador com esse e-mail!')</script>";
					echo"<script>window.location='$url'</script>";
				}
				$this->disconnect();
			}
			else
			{
				echo "
					<hr style='height:10%; visibility:hidden;'/>
					<div class='large-10 medium-10 small-10 large-centered columns'>
						<div align='center'>
							<form enctype='multipart/form-data' action='' method='POST'>
								<table width='450px'>
								<tr>
									<td valign='middle'>
										Primeiro Nome
									</td>
									<td>
										<input type='text' name='pnome' id='pnome' required autofocus/>
									</td>
								<tr
								<tr>
									<td valign='middle'>
										Último Nome
									</td>
									<td>
										<input type='text' name='unome' id='unome' required autofocus/>
									</td>
								<tr
								<tr>
									<td valign='middle'>
										Email
									</td>
									<td>
										<input type='text' name='email' id='email' required/>
									</td>
								<tr>
								<tr>
									<td valign='middle'>
										Curso
									</td>
									<td>
										<input type='text' name='curso' id='curso' required placeholder='ex: GPSI'/>
									</td>
								<tr>
								<tr>
									<td valign='middle'>
										Ciclo
									</td>
									<td>
										<input type='text' name='ciclo' id='ciclo'  onkeypress='return isNumberAndDashKey(event, this)' maxlength='5'   required placeholder='ex: 11-14'/>
									</td>
								<tr>
								<tr>
									<td valign='middle'>
										Password
									</td>
									<td>
										<input type='password' name='pw1' id='pw1' required/>
									</td>
								<tr>
								<tr>
									<td valign='middle'>
										Confirme a password
									</td>
									<td>
										<input type='password' name='pw2' id='pw2' required/>
									</td>
								<tr>
									<td align='center' colspan='2'>
										<input type='submit' name='submit' class='button' value='Confirmar'/>
									</td>
								</tr>
								</table>
							</form>
						</div>
					</div>
				<hr style='height:10%; visibility:hidden;'/>
				";
			}
		}
		
		function alterarDadosPessoais(){
			if (isset($_POST['email'])){
				$url = $this->prepareUrl("index.php?l=10", "ec");				
				$email = $_POST['email'];
				if ($_POST['pwd'] !=  "" && $_POST['pwd2'] !=  ""){				
					$x = $_POST['pwd'] != $_POST['pwd2'];
					if ($x == 1){
						echo"<script>alert('As password´s não coincidem')</script>";
					}else{
						//editar email e password
						$pw=md5($_POST['pwd']);
						$query = "update utilizadores set Email = '$email', Password = '$pw'
										where id = ".$_SESSION['ID'];
						$this->connect();
						$this->executarQuery($query);		
						$this->disconnect();
						$_SESSION['Email'] = $email;
						echo"<script>alert('E-mail e Password editados com sucesso')</script>";
					}
				}else{
					//editar email
					$query = "update utilizadores set Email = '$email' where id = ".$_SESSION['ID'];
					$this->connect();
					$this->executarQuery($query);		
					$this->disconnect();
					$_SESSION['Email'] = $email;
					echo"<script>alert('E-mail editado com sucesso')</script>";
				}	
				echo"<script>window.location='$url'</script>";								
			}else{
			echo "
					<div class='large-10 medium-10 small-10 large-centered columns'>
						<div align='center'>
							<form method='post' action=''>
								<table width='450px'>
								<tr>
									<td>E-mail</td>
									<td>
										<input type='text' name='email' value='".$_SESSION['Email']."' required/>
									</td>
								</tr>
								<tr>
									<td>Password</td>
									<td>
										<input type='password' name='pwd' id='pwd'/>
									</td>
								</tr>
								<tr>
									<td>Repita a Password</td>
									<td>
										<input type='password' name='pwd2' id='pwd2'/>
									</td>
								</tr>
								<tr>
									<td colspan='2' align='center'>
										<input type='submit' name='submit' class='button' value='Guardar'/>
									</td>
								</tr>
								</table>
							</form>
						</div>
					</div>
			";
			}
		}
		
	}
?>