SELECT `post_id` FROM `{%table%}` as p
	WHERE `post_id` > {%post_id%} AND p.`type` != 'tile'

	{%if 'page' == 'favourites'%}
		AND `user_id` = (SELECT `user_id` FROM `{%table%}` WHERE `post_id` = {%post_id%} LIMIT 1)
	{%endif%}

	{%if 'page' == 'posts'%}
		AND `user_id` = (SELECT `user_id` FROM `{%table%}` WHERE `post_id` = {%post_id%} LIMIT 1)
	{%endif%}

	{%if 'page' == 'tags'%}
		AND `description` LIKE '%#[{%tag_id%}]%'
	{%endif%}

	ORDER BY `post_id` ASC LIMIT 1;