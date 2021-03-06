<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="keywords" content="de correspondent, gedeelde artikelen, twitter, overzicht, gratis">
		<meta name="description" content="dcrrspndnt, indexer van gedeelde artikelen van De Correspondent, http://decorrespondent.nl", lees alle gedeelde artikelen op twitter gratis via http://molecule.nl/decorrespondent/>
		<meta name="author" content="xiffy">
		<title>de correspondent, de artikelen - populai op twitter</title>
		<link rel="stylesheet" href="./style2.css" />
		<link rel="alternate" type="application/rss+xml" title="Artikelen van De Correspondent - crrspndnt" href="./rss.php">

	</head>
	<body>


<?php
require_once('settings.local.php');
require_once('functions.php');
include('db.php');

$start = 0;
$qsa = '';
$disp = '';
$th_pubdate = '<th>Gepubliceerd</th>';
$sep = strstr($_SERVER['REQUEST_URI'], '?') ? '&amp;' : '?';
$th_tweets = '<th>tweets</th>';
$order_by = ' order by tweet_count desc ';

$mode = '';
$title = 'Populaire artikelen volgens twitter (all time)';

if(isset($_GET['mode']))
{
	$mode = $_GET['mode'];
	switch($mode)
	{
		case 'hour':
			$mode = ' where tweets.created_at > date_add(now(), interval -60 minute) ';
			$title = 'Populaire artikelen volgens twitter (laatste uur)';
			if (isset($_GET['disposition']))
			{
				$disp = (int) $_GET['disposition'];
				$low = ($disp + 1) * 60;
				$high = $disp * 60;
				$mode = 'where tweets.created_at > date_add(now(), interval -'.$low.' minute) and tweets.created_at < date_add(now(), interval -'.$high.' minute) ';
				$title = 'Populaire artikelen volgens twitter ('.$disp.' uur geleden)';
			}
			break;
		case 'day':
			$mode = ' where tweets.created_at > date_add(now(), interval -24 hour) ';
			$title = 'Populaire artikelen op \'de Correspondent\' volgens twitter (afgelopen 24 uur)';
			if (isset($_GET['disposition']))
			{
				$disp = (int) $_GET['disposition'];
				$low = ($disp + 1) * 24;
				$high = $disp * 24;
				$mode = 'where tweets.created_at > date_add(now(), interval -'.$low.' hour) and tweets.created_at < date_add(now(), interval -'.$high.' hour) ';
				$title = 'Populaire artikelen volgens twitter ('.$disp.' dag geleden)';
			}

			break;
		case 'week':
			$mode = ' where tweets.created_at > date_add(now(), interval -7 day) ';
			$title = 'Populaire artikelen volgens twitter ('.$disp.' week)';
			break;
		default:
			$mode = '';
	}
}

$i = 0;
$res = mysqli_query($db,'select artikelen.*, count(tweets.id) as tweet_count from artikelen left outer join tweets on tweets.art_id = artikelen.id '.$mode.' group by artikelen.id having tweet_count > 0 '.$order_by.' limit '.$start.',50');
?>
		<h1><?php echo $title;?> <a href="#footer" title="Klik en lees de verantwoording onderaan de pagina"> &#x15e3;</a><a href="https://twitter.com/dcrrspndnt" class="twitter-follow-button" data-show-count="false" data-lang="nl">Volg @dcrrspndnt</a></h1>
<?php include ('menu.php'); ?>
		<div class="center">
		<table>
			<tr>
				<?php echo $th_pubdate;?><th>Titel / Artikel</th><th>Auteur</th><th>Sectie</th><?php echo $th_tweets;?>
			</tr>
<?php
$tot_tweets = 0;
while($row = mysqli_fetch_array($res) )
{
	$og = unserialize(stripslashes($row['og']));
	$titel = isset($og['title']) ? $og['title'] : substr($row['clean_url'],26);
	$description = isset($og['description']) ? $og['description'] : 'Een mysterieus artikel';
	$auth_res = mysqli_query($db,'select * from meta_artikel left join meta on meta.ID = meta_artikel.meta_id where meta_artikel.art_id = ' .$row['ID']. ' and type = "article:author"');
	$author = mysqli_fetch_array($auth_res);
	$section_res = mysqli_query($db,'select * from meta_artikel left join meta on meta.ID = meta_artikel.meta_id where meta_artikel.art_id = ' .$row['ID']. ' and type = "article:section"');
	$section = mysqli_fetch_array($section_res);
	if(isset($og['article:published_time']))
	{
		if(strstr($og['article:published_time'], '-'))
		{
			$display_time = strftime('%e %b %H:%M', strtotime($og['article:published_time']));
		}
		else
		{
			$display_time = strftime('%e %b %H:%M', $og['article:published_time']);
		}
	}
	else
		$display_time = substr($row['created_at'],8,2).'-'.substr($row['created_at'],5,2).' '.substr($row['created_at'],11,5);
	$found_at = substr($row['created_at'],8,2).'-'.substr($row['created_at'],5,2).' '.substr($row['created_at'],11,5);
	?>

			<tr <?php if($i % 2 == 1) echo 'class="odd"'?>>
				<td><abbr title="gevonden op: <?php echo $found_at;?>"><?php echo $display_time ?></abbr></td>
				<td style="max-width:400px"><strong><a href="<?php echo $row['share_url'];?>" title="<?php echo $description ?>"><?php echo $titel ;?></a></strong></td>
				<td><a href="./meta_art.php?id=<?php echo $author['ID'];?>" title="alle artikelen van deze auteur"><?php echo $author['waarde'];?></a></td>
				<td><a href="./meta_art.php?id=<?php echo $section['ID'];?>" title="alle artikelen in deze sectie"><?php echo $section['waarde'];?></a></td>
				<td align="right"><?php echo $row['tweet_count']; $tot_tweets += (int)$row['tweet_count'];?></td>
			</tr>
	<?php
	$i++;
}
?>
			<tr><td colspan="4" align="right">totaal tweets:</td><td align="right"><strong><?php echo $tot_tweets;?></strong></tr>
			<tr>
				<td></td>
				<td colspan="3">per uur:
					<script>
						function goto_sel(selector) {
							var sel = document.getElementById(selector).selectedIndex;
							var uris = document.getElementById(selector).options;
							var goto = uris[sel].value;
							window.location=('top.php'+goto);
							return;
						}
					</script>
					<form class="disp_selector" method="GET" action="javascript:goto_sel('hour');" onsumbit="return goto_sel('hour')">
					<select id="hour">
						<option value="?mode=hour">afgelopen uur</option>
						<?php
						for ($i=1;$i<24;$i++)
						{
							$selected = ( $disp == $i ) ? ' selected="true" ' : '';
						?>
							<option value="?mode=hour&disposition=<?php echo $i;?>" <?php echo $selected;?>><?php echo $i;?> uur geleden</option>
						<?php
						}
						?>
					</select>
					<input type="submit" value="Toon"/>
					</form>
					per dag:
					<form class="disp_selector" method="GET" action="javascript:goto_sel('day');" onsumbit="return goto_sel('day')">
					<select id="day">
						<option value="?mode=day">afgelopen dag</option>
						<?php
						for ($i=1;$i<6;$i++)
						{
							$selected = ( $disp == $i ) ? ' selected="true" ' : '';
						?>
							<option value="?mode=day&disposition=<?php echo $i;?>" <?php echo $selected;?>><?php echo $i;?> dag geleden</option>
						<?php
						}
						?>
					</select>
					<input type="submit" value="Toon"/>
					</form>
				</td>
				<td></td>
			</tr>
		</table>


<?php include('search_box.php') ?>
	</div>
<?php include('footer.php') ?>
</body>
<?php @include('ga.inc.php') ?>

</html>