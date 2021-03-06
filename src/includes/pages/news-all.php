<?php
if(isset($user) && $user->isAdmin() && isset($args['submit']))
{
	$response = News::add(array(
		'title' => $args['newsTitle'],
		'author_id' => $user->id,
		'body' => $args['newsBody']
	));
	
	if($response == -1)
	{
		print "News failed to post";
	}
	else
	{
		print "1";
	}
}
else if(isset($user) && $user->isAdmin() && isset($args['delete']))
{
	if(News::delete($args['delete']))
	{
		print "News deleted";
	}
	else
	{
		print "Failed to delete news";
	}
}
else
{
	?>
	<script type="text/javascript">
	function expandNews(id)
	{
		$.get("lobby.php", { page: "get", get: "expandNews", id: id}, function(data){
			document.getElementById('news'+id).innerHTML = data;
		});
	}
	</script>
	<?php
	
	//get news
	$allNews = NewsCollection::all(true);
	
	foreach($allNews AS $news)
	{
		?>
		<div class="news">
			<div class="newsheader">
				<span class="title"><?=$news->title?></span><br>
				<span class="timestamp">By <?=User::getHandle($news->authorId)?> @ <?=date("d/m H:i:s",$news->timestamp)?></span>
			</div>
			<div class="newsbody" id="news<?=$news->id?>">
				<?=TextFormat::BBCode(TextFormat::previewText($news->body,300))?>
			</div>
			<div class="newsfooter">
				[<a href='#' onclick='expandNews(<?=$news->id?>); return false;'>Expand</a>] - 
				[<a href='#' onclick="loadWindow('news', ww(event), 'view=<?=$news->id?>')"><?=$news->countComments()?> Comments</a>]
				<?php
				if(isset($user) && $user->isAdmin())
				{ ?>
				- [<a href='#' onclick="confirm('Are you sure you want to delete this news article?')?loadWindow('news','delete=<?=$news->id?>'):false">Delete</a>]
				<?php
				} ?>
			</div>
		</div>
		<?php
	}
	
	if(isset($user) && $user->isAdmin())
	{
		?>
		<div class="form">
			<form action="" method="post">
				<div class="row">
					<span class="label">Title</span>
					<span class="data">
						<input type="text" id="newsTitle" value="" />
					</span>
				</div>
				<div class="row">
					<span class="label">Post</span>
					<span class="data">
						<textarea id="newsBody"></textarea>
					</span>
				</div>
				<div class="row">
					<span class="submit">
						<input type="button" id="submit" value="Post News" onclick="submitForm(['newsTitle','newsBody'], {page: 'news'}); return false;" />
					</span>
				</div>
			</form>
		</div>
		<?php
	}
}
?>
