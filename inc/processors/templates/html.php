<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?php echo $title; ?></title>
	<style><?php echo $style; ?></style>

</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
	<tr>
		<td align="center" valign="top">
			<table border="0" cellpadding="20" cellspacing="0" width="600" id="emailContainer">
				<tr>
					<td align="left" valign="top">
						<h2><?php echo $blogname; ?></h2>
						<p>Hello <?php echo $name; ?>,</p>
						<p>Have you seen these new training events on EYPD?</p>
						<ul>
							<?php
							foreach ( $events as $event ) {
								echo '<li><a href="' . $event['link'] . '">' . $event['title'] . '</a></li>';
							}
							?>
						</ul>
					</td>
				</tr>
				<tr>
					<td align="left" valign="top">
						<?php echo $template; ?>
					</td>
				</tr>
				<tr>
					<td>
						<p>To unsubscribe from these updates, please send an <a href="mailto:<?php echo $unsubscribe_link; ?>?Subject=Remove">email</a>
						</p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
