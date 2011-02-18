<?php
//get news
$news = new News($args['view']);

if(isset($args['submit']))
{
	$response = $news->postComment($args['commentText']);
	
	if($response == -1 || !Validate::isInt($response))
	{
		print "Comment failed to post";
	}
	else
	{
		print "1";
	}
}
else
{
?>
<p>
<a href="#" onclick="loadWindow('news');">Back</a>
</p>

<div class="news">
	<div class="newsheader">
		<span class="title"><?=$news->title?></span>
		<span class="timestamp">By <?=User::getHandle($news->authorId)?> @ <?=date("d/m H:i:s",$news->timestamp)?></span>
	</div>
	<div class="newsbody">
		<?=TextFormat::BBCode($news->body)?>
	</div>
</div>

<div class="comments">
	<?php
	//get comments
	$comments = $news->getComments();
	
	foreach($comments AS $comment)
	{
		?>
		<div class="userComment">
			<span class="author"><?=User::getHandle($comment->author)?> said...</span>
			
			<span class="body"><?=TextFormat::BBCode($comment->body)?></span>
			<span class="timestamp">@ <?=TextFormat::date($comment->timestamp)?></span>
		</div>	
		<?php
	}
	if(count($comments) == 0)
	{
		print "No Comments";
	}
	
	if(isset($user))
	{
	?>
	
	<div class="form">
		<form action="" method="post">
			<div class="row">
				<textarea id="commentText"></textarea>
			</div>
			<div class="row">
				<span class="submit">
					<input type="button" id="submit" value="Post Comment" onclick="submitForm(['commentText'], {page: 'news', view:<?=$args['view']?>}); return false;" />
				</span>
			</div>
		</form>
	</div>
	<?php
	}
	//not logged in
	else
	{
		print "<br><br>Login to post a comment";
	}
	?>
</div>
<?php
}
?>