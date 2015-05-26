<?php
	session_start();
	include_once('DataAccess.php');
	$da = new DataAccess();
?>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Repositório</title>
		<link rel="stylesheet" href="css/foundation.css" />
		<script src="js/vendor/modernizr.js"></script>
		<script src="js/onlyNumbers.js"></script>
		<?php
			$da->header();
		?>
	</head>
	<body>
		<?php
			if (isset($_GET['params']))
			{
				$da->setGET($_GET['params'],"ec"); 
			}
			
			if(!isset($_SESSION['ID']) && !isset($_GET['l']))
			{
				$da->login();
			}
			else
			{
				if (!isset($_GET['params']))
				{
					$da->entrada();
				}
			}
			
			if(isset($_GET['l']))
			{
				if(isset($_SESSION['ID']))
				{
					switch($_GET['l'])
					{
					case 1:
						$da->entrada();
						break;
					case 2:
						$da->pesquisa();
						break;
					case 3:
						$da->envio();
						break;
					case 4:
						$da->registro();
						break;
					case 5:
						$da->logout();
						break;
					case 6:
						$da->download();
						break;
					case 7:
						$da->envioapr();
						break;
					case 8:
						$da->eliminar();
						break;
					case 9:
						$da->enviorel();
						break;
					case 10:
						$da->alterarDadosPessoais();
						break;
					}
				}
				else
				{
					if($_GET['l'] == 4)
					{
						$da->registro();
					}
					else
					{
						echo"<script>alert('Tem de fazer login para ter acesso a esta página!')</script>";
						echo"<script>window.location='index.php'</script>";
					}
				}
			}
		?>
		<script src="js/vendor/jquery.js"></script>
		<script src="js/foundation.min.js"></script>
		<script>
		  $(document).foundation();
		</script>
	</body>
	<?php
		$da->footer();
	 ?>
</html>
