<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?php echo $title; ?></title>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
	<tr>
		<td align="center" valign="top">
			<table border="0" cellpadding="20" cellspacing="0" width="600" id="emailContainer">
				<tr>
					<td align="left" valign="top">
						<p>Hello <?php echo $name; ?>,</p>
						<p>Have you seen these new events posted?</p>
						<ul>
							<?php
							foreach ( $events as $event ) {
								echo "<li><a href='{$event['link']}'>{$event['title'] }</a></li>";

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
						<p>Do you wish to <a href="<?php echo $unsubscribe_link; ?>">unsubscribe</a> from these updates?</p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
