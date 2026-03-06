SELECT p.`post_id` as pid,p.*,m.*, (SELECT COUNT(`id`) FROM `{%t_likes%}` l WHERE l.`post_id` = p.`post_id`) AS likes, (SELECT COUNT(`id`) FROM `{%t_comm%}` c WHERE c.`post_id` = p.`post_id`) AS comments FROM `{%t_posts%}` p 
	
	LEFT JOIN `{%t_media%}` m ON m.`post_id` = p.`post_id`

	WHERE p.`user_id` = '{%user_id%}'

	AND 
	p.`type` != "tile"

	AND p.`user_id` NOT IN (SELECT b1.`profile_id` FROM `{%t_blocks%}` b1 WHERE b1.`user_id` = {%user_id%})

	AND p.`user_id` NOT IN (SELECT b2.`user_id` FROM `{%t_blocks%}` b2 WHERE b2.`profile_id` = {%user_id%})

	{%if s3save%}
	 AND p.`type` <> 'live'
    {%endif%}

	{%if offset%}
		AND p.`post_id` < {%offset%}
	{%endif%}

	GROUP BY p.`post_id` ORDER BY p.`post_id` DESC LIMIT {%total_limit%}