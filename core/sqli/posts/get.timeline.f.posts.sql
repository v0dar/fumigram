--
-- Sql query template featured timeline posts
--

SELECT u.`username`, u.`fname`, u.`lname`, p.`type`, p.`time`,p.`description` AS dsr,p.`post_id` AS pid,p.*,m.*,(SELECT COUNT(l.`id`) FROM `{%t_likes%}` l WHERE l.`post_id` = p.`post_id`) likes FROM `{%t_posts%}` p 
	
	LEFT JOIN `{%t_media%}` m ON m.`post_id` = p.`post_id`

	INNER JOIN  `{%t_users%}` u ON p.`user_id` = u.`user_id`

	WHERE u.`p_privacy` = '2' AND p.`type` <> 'live' AND p.`type` <> 'youtube' AND p.`type` <> 'vimeo' AND p.`type` <> 'dailymotion' AND p.`type` <> 'fetched' AND p.`type` <> 'gif' AND p.`type` != 'tile'

	{%if user_id%}

	AND p.`user_id` NOT IN (SELECT b1.`profile_id` FROM `{%t_blocks%}` b1 WHERE b1.`user_id` = {%user_id%})

	AND p.`user_id` NOT IN (SELECT b2.`user_id` FROM `{%t_blocks%}` b2 WHERE b2.`profile_id` = {%user_id%})

	AND p.`time` >= {%time_date%}
	
	{%endif%}

	ORDER BY likes DESC LIMIT 4;
