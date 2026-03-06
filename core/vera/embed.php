<?php 
/**
 * Embed class for importing content from other websites
 */
class embed extends Media{
	public $data = array( 'youtube' => '', 'vimeo' => '', 'dailymotion' => '', 'video_type' => '', 'thumbnail' => '', 'title' => '', 'description' => '', 'tags' => '', 'tags_array' => '', 'duration' => '');
	public function fetchVideo($link=''){
		global $config;
		if (preg_match('#(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})#i', $link, $match)) {
		    $this->data['youtube'] = self::secure($match[1]);
		    $this->data['video_type'] = 'youtube';
		} else if (preg_match("#https?://vimeo.com/([0-9]+)#i", $link, $match)) {
		    $this->data['vimeo'] = self::secure($match[1]);
		    $this->data['video_type'] = 'vimeo';
		} else if (preg_match('#https://www.dailymotion.com/video/([A-Za-z0-9]+)#s', $link, $match)) {
		    $this->data['dailymotion'] = self::secure($match[1]);
		    $this->data['video_type'] = 'daily';
		}
		    
	    if (!empty($this->data['youtube'])) {
	    	try {
				require_once('core/zipy/youtube-sdk/vendor/autoload.php');
	    		$youtube = new Madcoda\Youtube(array('key' => self::$config['yt_api']));
	            $vdata = $youtube->getVideoInfo($this->data['youtube']);
	            if (!empty($vdata)) {
		    		if (!empty($vdata->snippet)) {
	            		if (!empty($vdata->snippet->thumbnails->medium->url)) {
	            			$media = new Media();
	            			$this->data['images'] = $media->ImportImageAndCrop($vdata->snippet->thumbnails->medium->url);
	            		} 
		    			$info = $vdata->snippet;
						$this->data['tags']        = '';
		    			$this->data['title']       = $info->title;
						$this->data['description'] = $info->description;
		    			if (!empty(covtime($vdata->contentDetails->duration))) {
		    				$this->data['duration'] = covtime($vdata->contentDetails->duration);
		    			}
		    		}
		    	}
	    	}  catch (Exception $e) {
	    		//echo $e->getMessage();
	    	}
	    } else if (!empty($this->data['dailymotion'])) {
	    	$request = $this->curlConnect('https://api.dailymotion.com/video/' . $this->data['dailymotion'] . '?fields=thumbnail_large_url,title,duration,description,tags');
	    	if (!empty($request)) {
	    		if (!empty($request['title'])) {
	    			$this->data['title'] = $request['title'];
	    		}
	    		if (!empty($request['description'])) {
	    			$this->data['description'] = $request['description'];
	    		}
	    		if (!empty($request['thumbnail_large_url'])) {
	    			$media = new Media();
        			$this->data['images'] = $media->ImportImageAndCrop($request['thumbnail_large_url']);
	    		}
	    		if (!empty($request['duration'])) {
	    			$this->data['duration'] = gmdate("i:s", $request['duration']);
	    		}
	    		if (is_array($request['tags'])) {
					$this->data['tags'] = implode(',', $request['tags']);
				}
	    	}
	    } else if (!empty($this->data['vimeo'])) {
	    	$request = $this->curlConnect('http://vimeo.com/api/v2/video/' . $this->data['vimeo'] . '.json');
	    	if (!empty($request)) {
	    		$request = end($request);
	    		if (!empty($request['title'])) {
	    			$this->data['title'] = $request['title'];
	    		}
	    		if (!empty($request['description'])) {
	    			$this->data['description'] = $request['description'];
	    		}
	    		if (!empty($request['thumbnail_large'])) {
	    			$media = new Media();
        			$this->data['images'] = $media->ImportImageAndCrop($request['thumbnail_large']);
	    		}
	    		if (!empty($request['duration'])) {
	    			$this->data['duration'] = gmdate("i:s", $request['duration']);
	    		}
	    		if (!empty($request['tags'])) {
					$this->data['tags'] = $request['tags'];
				}
	    	}
	    } 
	}
}



