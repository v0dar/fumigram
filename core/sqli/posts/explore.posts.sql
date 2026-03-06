SELECT p.`post_id` AS pid,p.`user_id` uid,p.*,m.*,u.`username`,u.`user_id` owner_id,u.`subscribe_price`,u.`avatar`,(SELECT COUNT(l.`id`) FROM  `{%t_likes%}` l WHERE l.`post_id` = p.`post_id` ) AS likes, (SELECT COUNT(c.`id`) FROM `{%t_comm%}` c WHERE c.`post_id` = p.`post_id`) AS comments

	FROM `{%t_posts%}` p LEFT JOIN `{%t_media%}` m ON m.`post_id` = p.`post_id` 

	INNER JOIN `{%t_users%}` u ON p.`user_id` = u.`user_id` AND u.`p_privacy` > '0'

	WHERE (u.`p_privacy` = '2' {%if user_id%} OR p.`user_id` IN (SELECT f.`following_id` FROM `{%t_conn%}` f WHERE f.`follower_id` = {%user_id%} AND f.`type` = '1') OR p.`user_id` = {%user_id%} {%endif%}) AND p.`type` <> 'live'

	{%if offset%}
		AND p.`post_id` < {%offset%}
	{%endif%}

	{%if hashtag_id%}
		AND p.`description` LIKE CONCAT('%#[{%hashtag_id%}]%')
	{%endif%}

	{%if user_id%}

		{@ `Exclude posts from blocked users  or vice versa if user is logged in` @}
	 
		AND p.`user_id` NOT IN (SELECT b1.`profile_id` FROM `{%t_blocks%}` b1 WHERE b1.`user_id` = {%user_id%})

		AND p.`user_id` NOT IN (SELECT b2.`user_id` FROM `{%t_blocks%}` b2 WHERE b2.`profile_id` = {%user_id%})

	{%endif%}
	{%if hide_lock%}
		AND p.`price` = '0'
	{%endif%}

	GROUP BY p.`post_id` ORDER BY RAND() LIMIT {%total_limit%}