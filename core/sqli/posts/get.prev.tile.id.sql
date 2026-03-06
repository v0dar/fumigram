SELECT `post_id` FROM `{%table%}`
	WHERE `post_id` > {%post_id%}

	{%if 'page' == 'tiles'%}
		AND `type` = 'tile'
	{%endif%}

	ORDER BY `post_id` ASC LIMIT 1;