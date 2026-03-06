jQuery(document).ready(function($) {
	var hash = $('.csrf-token').val();
	$.ajaxSetup({ 
	    data: {hash: ((hash != undefined) ? hash : 0)},
	    cache: false,
	    timeout:(1000 * 360)
	});
	$(document).ajaxSend(function(e, xhr, opt) {
		if (opt.url.indexOf('main/follow') > 0 && not(is_logged())) {
			redirect('home');
			xhr.abort();
		}
	});
	$(document).on("click","[data-modal--menu-dismiss]",function(event) {
		$(this).closest('.modal--menu').removeClass('open');
	});
	$(document).on("click","[data-confirm--modal-dismiss]",function(event) {
		$(this).closest('.confirm--modal').fadeOut(500, function() {
			$(this).data('id', '');
		});
	});
	$(document).on('click',"[data-modal-menu]",function(event) {
		$(".modal--menu").each(function(index, el) {
			$(el).removeClass('open');
		});
		var modal = "#"+$(this).data('modal-menu');
		$(modal).addClass('open');
	});
	$(".caption").filter(function(){
    	if($.trim($(this).text()).length < 1){
        	$(this).text('')
        }
    });
    $(document).on('click', '.add-post-bf--controls', function(event) {
    	$(this).toggleClass('active');
    });
	delay(function(){
		if (is_logged()) {
			update_data();
		}
	},100);
	$(document).on('show.bs.dropdown', '.dropdown.slide', function(event) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideDown(400);
	});
	$(document).on('hide.bs.dropdown', '.dropdown.slide', function(event) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideUp(400);
	});
	$(document).on('click', '.lightbox-ol', function(event) {
		event.preventDefault();
		$('.light__box').remove();
		$('body').removeClass('scroll_stop');
	});
	$.fn.scroll2 = function (speed) {
        if (typeof(speed) === 'undefined')
            speed = 500;

        $('html, body').animate({
            scrollTop: ($(this).offset().top - 100)
        }, speed);

        return $(this);
    };
    $(window).scroll(function(event) {
    	if ($(this).scrollTop() > $(this).height()) {
    		$(".scroll__up").css('right', '25px');
    	}
    	else{
    		$(".scroll__up").css('right', '-100px');
    	}
    });
    $(".scroll__up").click(function(event) {
    	$("html,body").stop(/*stop animation*/).animate({scrollTop:0}, 800);
    });

    $("#search-chats").keyup(function(event) {
		var chatls = $(".chat-list").find('ul');
		var uname  = $(this).val();
		var found  = new Array();
		if (uname.length > 1) {
			chatls.find('span.username').each(function(index, el) {
				var username = $(el).text();
				if (username.indexOf(uname) == -1) {
					$(el).closest('li').addClass('hidden');
				}
			});
		}else{
			chatls.find('li').removeClass('hidden');
		}
	});

	$(document).on('submit','#edit-post-caption', function(event) {
		event.preventDefault();
		var text    = $(this).find('#caption').val();
		var post_id = $(this).find('#post_id').val();
		if (int(post_id) == 0) {
			return false;
		}
		var post = $("div[data-post-id='"+post_id+"']");
		$("#create-newpost").hide();
		$(this).empty();
		$('body').removeClass('active');
		// post.find('[data-caption]').replace('<br>', "\n", text);
		post.find('[data-caption]').html(linkify_htags(text));
		$.ajax({url: link('posts/update'),type: 'POST',dataType: 'json',data: {text:text,id:post_id}})
		.done(function(data) {
			if (data.message) {
				Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
			}
		});
	});

	$(document).on('click','.delete--post',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');
		var url = zis.parents('.confirm--modal').data('url');
		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(300);
			$.ajax({
				url: link('posts/delete-post'),
				type: 'POST',
				dataType: 'json',
				data: {post_id:id},
			})
			.done(function(data) {
				if (data.status == 200) {
					$("div[data-post-id='"+id+"']").slideUp(500,function(){
						$(this).remove();
						Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
					});
				}else{ Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});}
				if (url) {
					delay(function(){window.location.href = url;},1000);
				}
				$('body').removeClass('scroll_stop');
			});
		}
	});

	$(document).on('click','.delete--comment',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');
		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(200, function() {
				$("[data-post-comment='"+id+"']").slideUp(500,function(){
					$(this).remove();
				});
				var lbx = $('.new-comment');
				let count = int(lbx.text()) - 1;
				lbx.text(count);
				$.post(link('posts/delete-comment'), {id:id});
				$('.confirm--modal').css('display', 'none');
			});;
		}
	});

	$('body').on('mouseleave', '.udata', function(e) {
		var to = e.toElement || e.relatedTarget;
		if (!$(to).is(".user-details")) {
			clearTimeout($(this).data('timeout'));
			$('.user-details').remove();
		}
	});

	$('body').on('mouseleave', '.user-details', function() {
		$('.user-details').remove();
	});
	$('#tiles-cr').flickity({ freeScroll: true, pageDots: false, contain: true, resize: true, cellAlign: 'left', autoPlay: false, friction: 0.2 });
});

function get_cookie(name) {
    var matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return (matches ? decodeURIComponent(matches[1]) : undefined);
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*60*60*1000));
    var expires = "expires="+ d;
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookieValue(a) {
    var b = document.cookie.match('(^|;)\\s*' + a + '\\s*=\\s*([^;]+)');
    return b ? b.pop() : '';
}

function is_logged(){
	if (get_cookie('user_id') != undefined) {
		return true;
	}
	return false;
}

function scroll2top() {
	verticalOffset = typeof (verticalOffset) != 'undefined' ? verticalOffset : 0;
	element = $('html');
	offset = element.offset();
	offsetTop = offset.top;
	$('html, body').animate({ scrollTop: offsetTop }, 500, 'linear');
}

function base64_2_blob(dataURI) {
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0)
        byteString = atob(dataURI.split(',')[1]);
    else
        byteString = unescape(dataURI.split(',')[1]);

    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ia], { type:mimeString });
}

function video_base64_iamge(video) {
	if (!video) {
		return false;
	}
	var canvas    = document.createElement("canvas");
	var scale     = 0.25;
	var b64       = "";
	canvas.width  = video.videoWidth * scale;
	canvas.height = video.videoHeight * scale;
	canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
	b64 = canvas.toDataURL();
	return b64;
}

function youtube(url){
	if (!url) { return false;}
	var regex  = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
	var groups = regex.exec(url);
	return (Array.isArray(groups)) ? groups[7] : false;
}

function vimeo(url){
	if (!url) { return false;}
	var regex  = /^https?:\/\/vimeo\.com\/([0-9]+)\/{0,1}$/;
	var groups = regex.exec(url);
	return (Array.isArray(groups)) ? groups[1] : false;
}

function dailymotion(url){
	if (!url) { return false;}
	var regex  = /^(?:https?:\/\/)?www\.dailymotion\.com\/video\/([a-zA-Z0-9_]+)\/?$/;
	var groups = regex.exec(url);
	return (Array.isArray(groups)) ? groups[1] : false;
}

function is_mp4_url(url){
	if (!url) { return false;}
	var regex = /^(http:\/\/|https:\/\/|www\.).*(\.mp4)$/;
	var groups = regex.exec(url);
	return (Array.isArray(groups) && groups[2] == '.mp4') ? true : false;
}

function delete_post(id,redir){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (!id) { return false; }
	$('div.delpost--modal').data('id',id).fadeIn(350);
	if (redir === true) {
		$('div.delpost--modal').data('url',site_url());
	}
}

function embed_post(id,redir){
	if (!id) { return false; }
	$('#embed_post').val('<iframe src="'+site_url( 'embed/'+id )+'" style="width: 100%;height: 100%;"></iframe>');
	$('div.embedpost--modal').data('id',id).fadeIn(350);
	if (redir === true) {
		$('div.embedpost--modal').data('url',site_url());
	}
}

function comment_post(id,event,type = ''){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (((event.keyCode == 13 && event.shiftKey == 0) || type == 'send') && id) {
		event.preventDefault();
		var post = $("div[data-post-id='"+id+"']");
		post.find('.commenting-overlay').removeClass('hidden');
		var text = $('[id=vote-postf-' + id + ']').find('.comment').val();
		var list = post.find('.post-comments-list');
		post.find('.comment').val('');
		$.post(rita() + 'posts/add-comment', {post_id:id,text:text}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				$(data.html).insertAfter(list.find('li.pp_post_comms'));
				if (list.css('display') == 'none') {
					list.slideDown(50);
				}
			}
			$('.comment').html('');
			post.find('.commenting-overlay').addClass('hidden');
		});
	} else {
		return false;
	}
}

function delete_commnet(id){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (id) {
		$('.delcomment--modal').data('id', id).fadeIn(350);
	}else{
		return false;
	}
}

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

function lightbox(post_id,page){
	if (!post_id || !page) { return false; }
	$("#modal-progress").removeClass('hidden');
	$.get((rita() + 'posts/lightbox'), {post_id:post_id,page:page},function(data) {
		if (data.status == 200) {
			$(".lightbox__container").html(data.html);
			$('body').addClass('scroll_stop');
		}
		else{
			$(".lightbox__container").empty();
		}
		$("#modal-progress").addClass('hidden');
	},dataType= 'json');
	window.history.pushState({state:'new'},'', site_url( 'post/'+post_id ));
}

function storelightbox(post_id,page){
    if (!post_id || !page) { return false; }

    $("#modal-progress").removeClass('hidden');
    $.get((rita() + 'store/lightbox'), {post_id:post_id,page:page},function(data) {
        if (data.status === 200) {
            $(".lightbox__container").html(data.html);
			$('body').addClass('scroll_stop');
        }
        else{
            $(".lightbox__container").empty();
        }
        $("#modal-progress").addClass('hidden');
    },dataType= 'json');
    window.history.pushState({state:'new'},'', site_url( 'store/'+post_id ));
}

function Pxpx_GetPayPalLink(type,amount,_title,_id) {
    $('.btn-paypal').attr('disabled','true');
    if (amount > 0) {
        $.post(link('store/get_paypal_link'), {type: type,amount:amount,title:_title,id:_id}, function(data, textStatus, xhr) {
            if (data.status == 200) {
                window.location.href = data.url;
            }
            $('.btn-paypal').removeAttr('disabled');
        });
    }
    else{
        scroll2top();
        $.toast("{{LANG please_check_details}}",{
            duration: 5000,
            type: 'success',
            align: 'bottom',
            singleton: true
        });
    }
}

function lb_comment(id,event){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (event.keyCode == 13 && event.shiftKey == 0 && id) {
		event.preventDefault();
		var lb = $("div.light__box");
		lb.find('.commenting-overlay').removeClass('hidden');
		var text = lb.find('.comment_light').val();
		var list = lb.find('.post-comments-list');
		if (!text) { return false; }
		$.post(rita() + 'posts/add-comment', {post_id:id,text:text}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				var lbx = $('.new-comment');
				let count = int(lbx.text()) + 1;
				lbx.text(count);
				if (list.find('.pp_light_comm_count').length > 0) {
					$(data.html).insertAfter(list.find('.pp_light_comm_count'));
				}else{
					list.prepend(data.html);
				}
			}
			lb.find('.comment_light').val('');
			lb.find('.commenting-overlay').addClass('hidden');
		});
	} else{
		return false;
	}
}

function scroll_el(object,speed){
	if (!speed) {
		speed = 1000;
	}
	object.animate({
		scrollTop: (object.get(0).scrollHeight)
	}, speed);
}

function not(val){
	return !val;
}

function randint(min, max) {
  return Math.floor(Math.random() * (max - min)) + min;
}

function random_color(){
	var cols = [
		'#5bbe89',
		'#d55f3a',
		'#a97cc6',
		'#ef863c',
		'#e27100',
		'#15493b',
		'#b582af',
	    '#a84849',
	    '#fc9cde',
	    '#f9c270',
	    '#70a0e0',
	    '#56c4c5',
	    '#51bcbc',
	    '#f33d4c',
	    '#a1ce79',
	    '#a085e2',
	    '#ed9e6a',
	    '#2b87ce',
	    '#f2812b',
	    '#0ba05d',
	    '#f9a722',
	    '#8ec96c',
	    '#01a5a5',
	    '#5462a5',
	    '#609b41',
	    '#ff72d2',
	    '#008484',
	    '#c9605e',
	    '#aa2294',
	    '#056bba',
	    '#0e71ea'
	];
	return cols[randint(0,(cols.length - 1))];
}


function log(val){
	console.log(val);
}

function int(val){
	if ($.isNumeric(val) === true) {
		val = Number(val);
	}
	else{ val = 0; }
	return val;
}

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

function notifications(){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	$(".loading-data").html(`<div class="bouncing-jelly" style="color: var(--fumigram)"><div></div><div></div></div>`);
	var notfi_set = $("ul#notifications__list");
	var newnotif  = $("#new__notif");
	$.ajax({
		url: rita() + 'main/get_notif',
		type: 'GET',
		dataType: 'json'
	})
	.done(function(data) {
		if (data.status == 200) {
			setTimeout(function (){
				$(".loading-data").html('');
				newnotif.text('');
				notfi_set.html(data.html);
			}, 600);
		}
		else if(data.status == 304){
			setTimeout(function (){
				$(".loading-data").html('');
				notfi_set.addClass('notify-scroll');
				notfi_set.html('<h5 class="empty_state"><svg xmlns="http://www.w3.org/2000/svg" class="confetti" viewBox="0 0 1081 601"><path class="st0" d="M711.8 91.5c9.2 0 16.7-7.5 16.7-16.7s-7.5-16.7-16.7-16.7 -16.7 7.5-16.7 16.7C695.2 84 702.7 91.5 711.8 91.5zM711.8 64.1c5.9 0 10.7 4.8 10.7 10.7s-4.8 10.7-10.7 10.7 -10.7-4.8-10.7-10.7S705.9 64.1 711.8 64.1z"/><path class="st0" d="M74.5 108.3c9.2 0 16.7-7.5 16.7-16.7s-7.5-16.7-16.7-16.7 -16.7 7.5-16.7 16.7C57.9 100.9 65.3 108.3 74.5 108.3zM74.5 81c5.9 0 10.7 4.8 10.7 10.7 0 5.9-4.8 10.7-10.7 10.7s-10.7-4.8-10.7-10.7S68.6 81 74.5 81z"/><path class="st1" d="M303 146.1c9.2 0 16.7-7.5 16.7-16.7s-7.5-16.7-16.7-16.7 -16.7 7.5-16.7 16.7C286.4 138.6 293.8 146.1 303 146.1zM303 118.7c5.9 0 10.7 4.8 10.7 10.7 0 5.9-4.8 10.7-10.7 10.7s-10.7-4.8-10.7-10.7C292.3 123.5 297.1 118.7 303 118.7z"/><path class="st2" d="M243.4 347.4c9.2 0 16.7-7.5 16.7-16.7s-7.5-16.7-16.7-16.7 -16.7 7.5-16.7 16.7S234.2 347.4 243.4 347.4zM243.4 320c5.9 0 10.7 4.8 10.7 10.7 0 5.9-4.8 10.7-10.7 10.7s-10.7-4.8-10.7-10.7S237.5 320 243.4 320z"/><path class="st1" d="M809.8 542.3c9.2 0 16.7-7.5 16.7-16.7s-7.5-16.7-16.7-16.7 -16.7 7.5-16.7 16.7C793.2 534.8 800.7 542.3 809.8 542.3zM809.8 514.9c5.9 0 10.7 4.8 10.7 10.7s-4.8 10.7-10.7 10.7 -10.7-4.8-10.7-10.7S803.9 514.9 809.8 514.9z"/><path class="st3" d="M1060.5 548.3c9.2 0 16.7-7.5 16.7-16.7s-7.5-16.7-16.7-16.7 -16.7 7.5-16.7 16.7C1043.9 540.8 1051.4 548.3 1060.5 548.3zM1060.5 520.9c5.9 0 10.7 4.8 10.7 10.7s-4.8 10.7-10.7 10.7 -10.7-4.8-10.7-10.7S1054.6 520.9 1060.5 520.9z"/><path class="st3" d="M387.9 25.2l7.4-7.4c1.1-1.1 1.1-3 0-4.1s-3-1.1-4.1 0l-7.4 7.4 -7.4-7.4c-1.1-1.1-3-1.1-4.1 0s-1.1 3 0 4.1l7.4 7.4 -7.4 7.4c-1.1 1.1-1.1 3 0 4.1s3 1.1 4.1 0l7.4-7.4 7.4 7.4c1.1 1.1 3 1.1 4.1 0s1.1-3 0-4.1L387.9 25.2z"/><path class="st3" d="M368.3 498.6l7.4-7.4c1.1-1.1 1.1-3 0-4.1s-3-1.1-4.1 0l-7.4 7.4 -7.4-7.4c-1.1-1.1-3-1.1-4.1 0s-1.1 3 0 4.1l7.4 7.4 -7.4 7.4c-1.1 1.1-1.1 3 0 4.1s3 1.1 4.1 0l7.4-7.4 7.4 7.4c1.1 1.1 3 1.1 4.1 0s1.1-3 0-4.1L368.3 498.6z"/><path class="st3" d="M16.4 270.2l7.4-7.4c1.1-1.1 1.1-3 0-4.1s-3-1.1-4.1 0l-7.4 7.4 -7.4-7.4c-1.1-1.1-3-1.1-4.1 0s-1.1 3 0 4.1l7.4 7.4 -7.4 7.4c-1.1 1.1-1.1 3 0 4.1s3 1.1 4.1 0l7.4-7.4 7.4 7.4c1.1 1.1 3 1.1 4.1 0s1.1-3 0-4.1L16.4 270.2z"/><path class="st2" d="M824.7 351.1l7.4-7.4c1.1-1.1 1.1-3 0-4.1s-3-1.1-4.1 0l-7.4 7.4 -7.4-7.4c-1.1-1.1-3-1.1-4.1 0s-1.1 3 0 4.1l7.4 7.4 -7.4 7.4c-1.1 1.1-1.1 3 0 4.1s3 1.1 4.1 0l7.4-7.4 7.4 7.4c1.1 1.1 3 1.1 4.1 0s1.1-3 0-4.1L824.7 351.1z"/><path class="st1" d="M146.3 573.6H138v-8.3c0-1.3-1-2.3-2.3-2.3s-2.3 1-2.3 2.3v8.3h-8.3c-1.3 0-2.3 1-2.3 2.3s1 2.3 2.3 2.3h8.3v8.3c0 1.3 1 2.3 2.3 2.3s2.3-1 2.3-2.3v-8.3h8.3c1.3 0 2.3-1 2.3-2.3S147.6 573.6 146.3 573.6z"/><path class="st1" d="M1005.6 76.3h-8.3V68c0-1.3-1-2.3-2.3-2.3s-2.3 1-2.3 2.3v8.3h-8.3c-1.3 0-2.3 1-2.3 2.3s1 2.3 2.3 2.3h8.3v8.3c0 1.3 1 2.3 2.3 2.3s2.3-1 2.3-2.3v-8.3h8.3c1.3 0 2.3-1 2.3-2.3S1006.8 76.3 1005.6 76.3z"/><path class="st1" d="M95.5 251.6c-3.5 0-6.3 2.8-6.3 6.3 0 3.5 2.8 6.3 6.3 6.3s6.3-2.8 6.3-6.3S99 251.6 95.5 251.6z"/><path class="st0" d="M1032 281.8c-3.5 0-6.3 2.8-6.3 6.3s2.8 6.3 6.3 6.3 6.3-2.8 6.3-6.3S1035.5 281.8 1032 281.8z"/><path class="st2" d="M741.6 139.3c-3.5 0-6.3 2.8-6.3 6.3s2.8 6.3 6.3 6.3 6.3-2.8 6.3-6.3S745 139.3 741.6 139.3z"/><path class="st3" d="M890.7 43.5c3.3 0 6-2.7 6-6s-2.7-6-6-6 -6 2.7-6 6C884.8 40.8 887.4 43.5 890.7 43.5z"/><path class="st0" d="M164.3 537.6c3.3 0 6-2.7 6-6s-2.7-6-6-6 -6 2.7-6 6C158.4 535 161 537.6 164.3 537.6z"/></svg><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell-off" style="display: block;margin: -80px auto 40px;width: 60px;height: 60px;color: #313549;"><path d="M8.56 2.9A7 7 0 0 1 19 9v4m-2 4H2a3 3 0 0 0 3-3V9a7 7 0 0 1 .78-3.22M13.73 21a2 2 0 0 1-3.46 0"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>' + data.message + '</h5>');
			}, 1000);
		}
	}).fail(function (qXHR, textStatus, errorThrown) {});
}

function notif_cl(id){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (!id) {return false;}
	$.ajax({url: link('main/click_notif'),type: 'POST',dataType: 'json',data: {id:id}})
	.done(function(data) {
		if (data.status == 200) {
			var notfi_dot = $(".noti-dot");
			notfi_dot.html('');
		}
	}).fail(function (qXHR, textStatus, errorThrown) {});
}

function mark_read(){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	$.ajax({url: link('main/mark_read'),type: 'POST',dataType: 'json'})
	.done(function(data) {
		if (data.status == 200) {
			var notfi_dot = $(".noti-dot");
			notfi_dot.html('');
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		} else {
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		}
	}).fail(function (qXHR, textStatus, errorThrown) {console.log(errorThrown)});
}

function notify_follow(notifier_id){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (!notifier_id) {return false;}
	$.ajax({url: link('main/notify_follow'),type: 'POST',dataType: 'json',data: {notifier_id:notifier_id}})
	.done(function(data) {
		if (data.status == 200) {
			var notfi_dot = $(".noti-dot");
			notfi_dot.html('');
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		} else {
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		}
	}).fail(function (qXHR, textStatus, errorThrown) {});
}

function Pxp_AcceptFollowRequest(self,user_id) {
	$('.accept_request_btn').attr('disabled', 'true');
	$.post(rita() + 'main/accept_requests', {user_id: user_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			$('#request_menu_'+user_id).remove();
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		}
		else{
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		}
	});
}

function Pxp_DeleteFollowRequest(self,user_id) {
	$('.accept_request_btn').attr('disabled', 'true');
	$.post(rita() + 'main/delete_requests', {user_id: user_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			$('#request_menu_'+user_id).remove();
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		}
		else{
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		}
	});
}
function like_post(post_id,zis){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (!post_id || !zis) {return false;}
	var zis = $(zis);
	var lks = null;
	if ($("[data-post-likes='"+post_id+"']").length == 1) {
		lks = $("[data-post-likes='"+post_id+"']");
	}
	if (zis.hasClass('active')) {
		zis.removeClass('active');
		if (lks) {
			let likes = int(lks.text());
			if (likes >= 1) {
				lks.text(likes - 1);
			}
		}
	}else{
		zis.addClass('active');
		if (lks) {
			let likes = int(lks.text()) + 1;
			lks.text(likes);
		}
	}
	$.ajax({url: rita() + 'posts/like',type: 'POST',dataType: 'json',data: {id:post_id}})
	.done(function(data) {});
}

function star_post(post_id,zis){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (!post_id || !zis) {return false;}
	var zis = $(zis);
	if (zis.hasClass('active')) {
		zis.removeClass('active');
	}
	else{
		zis.addClass('active');
	}
	$.ajax({url: link('posts/star'),type: 'POST',dataType: 'json',data: {id:post_id}})
	.done(function(data) {
		if (data.status == 200) {
			Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
		}
	});
}

function update_data(){
	var app_page = $("body").data('app');
	var features = {
		'chats':1,
		'stories':0,
		'new_points':1,
		'new_messages':1,
		'notifications':1,
	};
	if (app_page == 'messages') {
		features['chats'] = 1;
	}
	$.ajax({url: link('main/update-data'),type: 'GET',dataType: 'json',data: features})
	.done(function(data) {
		if (data.notif && $.isNumeric(data.notif)) {
			var newnotif = $("#new__notif");
			newnotif.text(data.notif);
		}
		if (data.points) {
			var points = new Intl.NumberFormat().format(data.points);
			var topbar = $("#nova-points");
			topbar.text(points);
		}
		if (data.new_messages && $.isNumeric(data.new_messages)) {
			var new_messages = $("#new__messages");
			var new_messages_sec = $("#new__messages_nav");
			new_messages.text(data.new_messages);
			new_messages_sec.text(data.new_messages);
		}			
	});
	setTimeout(function(){
		update_data();
	},(500 * 10))
}

function link(path){
	var url = rita() + path;
	return url;
}

function redirect(path){
	window.location.href = site_url(path);
}

function header_loadbar(a){
	if (a == 'show') {
		$('body').addClass('app-loading');
	}
	else{
		$('body').removeClass('app-loading');
	}
}

function view_post_likes(post_id){
	if (post_id && $.isNumeric(post_id)) {
		if ($('[data-post-likes='+post_id+']').text() > 0) {
			header_loadbar('show');
			$.ajax({
				url: link('posts/view-likes'),
				type: 'GET',
				dataType: 'json',
				data: {post_id:post_id},
			}).done(function(data) {
				if (data.status == 200) {
					$(data.html).insertAfter('main.container');
				}else{
					if (data.message) {
						Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.message+'</div>'});
					}
				}
				delay(function(){
					header_loadbar();
				},500);
			});
		}
		
	}
}

function toggle_post_comm(post_id) {
	if (post_id && $.isNumeric(post_id)) {
		$('.timeline-posts[data-post-id="'+post_id+'"]').find('.post-comments-list').slideToggle(300);
	}
}

function load_tlp_comments(post_id,zis) {
	if (post_id && $.isNumeric(post_id) && zis) {
		var post = $('.timeline-posts[data-post-id="'+post_id+'"]');
        var first = post.find('.post-comments-list').find('[data-post-comment]').first();
		var last = post.find('.post-comments-list').find('[data-post-comment]').last();
		var cmid = last.data('post-comment');
		var zis  = $(zis);
		if ($.isNumeric(cmid)) {
			zis.attr('disabled', 'true');
			$.ajax({
				url: link('posts/load-tlp-comments'),
				type: 'POST',
				dataType: 'json',
				data: {post_id: post_id,offset:cmid},
			}).done(function(data) {
				if (data.status == 200) {
					$(data.html).insertAfter(last);
                    $(data.html).insertAfter(first);
				} else {
					zis.text(data.message);
					delay(function(){
						zis.fadeOut(300, function() {
							$(this).parent('li').remove();
						});
					},3000);
				}
				zis.removeAttr('disabled');
			});
		}
	}
}

function edit_post(post_id){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}

	if (post_id && $.isNumeric(post_id)) {
		var form = $("#post-editing").html();
		var post = $("div[data-post-id='"+post_id+"']");
		var text = post.find('[data-caption]').text();
		if (form.length) {
			form = $(form);
			form.find('#caption').val($.trim(text));
			form.find('#post_id').val(post_id);
			$('body').addClass('active');
			$("#create-newpost").html(form).fadeIn(300);
		}
	}
}

function linkify_htags(text){
	var htag  = '';
	var htags = text.match(/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]{4,120})/ig);
	if (Array.isArray(htags) && htags.length > 0) {
		htags.forEach(function(el){
			htag = el.substr(1);
			htag = '<a href="'+site_url(('explore/tags/'+htag))+'">'+el+'</a>';
			text = text.replace(el, htag);
		});
	}
	return text;
}

function px_add_plays(post_id) {
	$.ajax({
		url: link('posts/add_plays'),
		type: 'POST',
		dataType: 'json',
		data: {post_id:post_id},
	})
	.done(function(data) {
		if (data.status == 200) {
			$('.rx_plays').html(data.count);
			$('.video_plays_').find('span').html(data.count);
		}
		else{}
	});
}
function px_add_views(user_id) {
	$.ajax({
		url: link('posts/add_views'),
		type: 'POST',
		dataType: 'json',
		data: {user_id:user_id},
	}).done(function(data) {
		if (data.status == 200) {
			$('._uvisit').html(data.count)
		}
		else{
		}
	});
}

function get_more_activities() {
	var id = $('.activity_').last().attr('id');
	$('#load_more_activities_').hide();
	$("#load_more_activities_load_").removeClass('hidden');
	$.post(link('main/activities'),{id:id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			$('#load_more_activities_').show();
			setTimeout(function () {
			    $('#activities_container').append(data.html);
				scroll_el($('#activities_container'),700);
		    }, 2000);
		} else {
			if ($('#load_more_activities_text').length == 0) {
				$('#activities_container').append('<div id="load_more_activities_text" class="item activity_"><div class="caption caption_ text-center">'+data.text+'</div></div>');
                $('#load_more_activities_').hide();
				scroll_el($('#activities_container'),700);
			}
        }
		setTimeout(function () {
			$("#load_more_activities_load_").addClass('hidden');
		}, 2000);
	});
}

function show_m_reprted(post_id) {
	$('.show_m_reprted-'+post_id).remove(); 
	$('.text_m_reprted-'+post_id).remove(); 
}

// like comment 
function like_dis_comment(comment_id,self) {
	$.post(link('comments/like_dislike'),{comment_id:comment_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			if (data.code == 1) {
				$('.comment_like_'+comment_id).find('svg').addClass('liked_color');
				$('.comment_like_span_'+comment_id).find('span').html(data.likes);
			} else{
				$('.comment_like_'+comment_id).find('svg').removeClass('liked_color');
				$('.comment_like_span_'+comment_id).find('span').html(data.likes);
			}
		}
	});
} 

// reply comment 
function reply_comment(id,event,type = ''){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (event.keyCode == 13 && event.shiftKey == 0 && id) {
		event.preventDefault();
		var comment = $("[data-post-comment='"+id+"']");
		comment.find('.comment').attr('disabled', true);
		comment.find('.reply_commenting_overlay').removeClass('hidden');
		var text = comment.find('.comment').val();
		var list = comment.find('.reply_list');
		var lbx = comment.find('.repli_'+id);
		comment.find('.comment').val('');
		$.post(rita() + 'comments/add_comment_reply', {comment_id:id,text:text}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				if (type == 'lightbox') {
					let count = int(lbx.text()) + 1;
					lbx.text(count);
					$('.lightbox_replies_'+id).append(data.html);
				} else{
					let count = int(lbx.text()) + 1;
					lbx.text(count);
					list.append(data.html);
				}
			}
			comment.find('.reply_commenting_overlay').addClass('hidden');
			comment.find('.comment').removeAttr('disabled');
			$('.commenting-overlay').addClass('hidden');
		});
	} else{
		return false;
	}
}

function get_comment_reply(comment_id,self,type = ''){
	var comment = $("[data-post-comment='"+comment_id+"']");
	var list = comment.find('.reply_list');
	if (list.text() == '' && type != 'lightbox') {
		$.post(link('comments/comment_reply'),{comment_id:comment_id}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				if (type == 'lightbox') {
					$('.lightbox_replies_'+comment_id).html(data.html);
					$('#box_reply_form_'+comment_id).show();
				} else{
					list.html(data.html);
					$('#add_reply_form_'+comment_id).show();
				}
			}
		});
	} else if($('.lightbox_replies_'+comment_id).text() == '' && type == 'lightbox'){
		$.post(link('comments/comment_reply'),{comment_id:comment_id}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				if (type == 'lightbox') {
					$('.lightbox_replies_'+comment_id).html(data.html);
					$('#box_reply_form_'+comment_id).show();
				} else{
					list.html(data.html);
					$('#add_reply_form_'+comment_id).show();
				}
			}
		});

	} else{
		if (type == 'lightbox') {
			$('.lightbox_replies_'+comment_id).html('');
			$('#box_reply_form_'+comment_id).hide();
		} else{
			list.html('');
			$('#add_reply_form_'+comment_id).hide();
		}
	}
}

function like_dis_comment_reply(reply_id,self) {
	$.post(link('comments/reply_like_dislike'),{reply_id:reply_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			if (data.code == 1) {
				$(self).find('svg').addClass('liked_color');
				$('.comment_like_span_reply_'+reply_id).find('span').html(data.likes);
			}
			else{
				$(self).find('svg').removeClass('liked_color');
				$('.comment_like_span_reply_'+reply_id).find('span').html(data.likes);
			}
		}
	});
}

function delete_commnet_reply(id){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	if (id) {
		$('.delreply--modal').data('id', id).fadeIn(450);
	} else{
		return false;
	}
}

function pxp_boost_post(post_id){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	$.post(link('posts/boost'), {post_id: post_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			if (data.code == 1) {
				Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.success+'</div>'});
				$('#boost_'+post_id).html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="feather feather-compass" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>&nbsp;&nbsp;&nbsp;<div><b>' + data.message + '</b><p>' + data.subtitle + '</p></div>');
			} else if (data.code == 2) {
				Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.success+'</div>'});
				$('#boost_'+post_id).html('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="feather"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.33 5.43981C16.1659 4.81287 16.7517 4.37451 17.2138 4.08957C17.6856 3.79863 17.9076 3.74762 18.026 3.75008C18.4011 3.75788 18.7529 3.93377 18.9842 4.22919C19.0572 4.32242 19.1496 4.53062 19.1999 5.08266C19.2492 5.62331 19.25 6.35492 19.25 7.39981L19.25 7.99985L19.25 13.9998L19.25 14.5998C19.25 15.6447 19.2492 16.3763 19.1999 16.917C19.1496 17.469 19.0572 17.6772 18.9842 17.7704C18.7529 18.0659 18.4011 18.2417 18.026 18.2495C17.9076 18.252 17.6856 18.201 17.2138 17.9101C16.7517 17.6251 16.1659 17.1868 15.33 16.5598L12.75 14.6248L12.75 7.37481L15.33 5.43981ZM20.6937 4.94641C20.7493 5.55603 20.75 6.34469 20.75 7.32486C22.4617 7.67232 23.75 9.18563 23.75 10.9998C23.75 12.8141 22.4617 14.3274 20.75 14.6748C20.75 15.655 20.7493 16.4436 20.6937 17.0532C20.6378 17.6669 20.5175 18.2453 20.1652 18.6952C19.6563 19.3451 18.8824 19.7321 18.0572 19.7492C17.4859 19.7611 16.951 19.5103 16.4265 19.1868C15.8991 18.8617 15.2588 18.3814 14.4614 17.7833L14.4613 17.7833L14.4612 17.7832L14.43 17.7598L11.7502 15.75L9.75 15.75L9.75 19.5001C9.75 20.7427 8.74264 21.7501 7.5 21.7501C6.25736 21.7501 5.25 20.7427 5.25 19.5001L5.25 15.6911C2.98301 15.3315 1.25 13.3681 1.25 11C1.25 8.37663 3.37665 6.24998 6 6.24998L11.7498 6.24998L14.43 4.23981L14.4612 4.2164C15.2587 3.61828 15.8991 3.13799 16.4265 2.81278C16.951 2.48934 17.4859 2.23853 18.0572 2.25041C18.8824 2.26757 19.6563 2.65452 20.1652 3.30444C20.5175 3.75436 20.6378 4.33271 20.6937 4.94641Z" fill="currentColor"></path></svg>&nbsp;&nbsp;&nbsp;<div><b>' + data.message + '</b><p>' + data.subtitle + '</p></div>');
			} else if (data.code == 3) {
				Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.success+'</div>'});
				$('#boost_'+post_id).html('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="feather"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.33 5.43981C16.1659 4.81287 16.7517 4.37451 17.2138 4.08957C17.6856 3.79863 17.9076 3.74762 18.026 3.75008C18.4011 3.75788 18.7529 3.93377 18.9842 4.22919C19.0572 4.32242 19.1496 4.53062 19.1999 5.08266C19.2492 5.62331 19.25 6.35492 19.25 7.39981L19.25 7.99985L19.25 13.9998L19.25 14.5998C19.25 15.6447 19.2492 16.3763 19.1999 16.917C19.1496 17.469 19.0572 17.6772 18.9842 17.7704C18.7529 18.0659 18.4011 18.2417 18.026 18.2495C17.9076 18.252 17.6856 18.201 17.2138 17.9101C16.7517 17.6251 16.1659 17.1868 15.33 16.5598L12.75 14.6248L12.75 7.37481L15.33 5.43981ZM20.6937 4.94641C20.7493 5.55603 20.75 6.34469 20.75 7.32486C22.4617 7.67232 23.75 9.18563 23.75 10.9998C23.75 12.8141 22.4617 14.3274 20.75 14.6748C20.75 15.655 20.7493 16.4436 20.6937 17.0532C20.6378 17.6669 20.5175 18.2453 20.1652 18.6952C19.6563 19.3451 18.8824 19.7321 18.0572 19.7492C17.4859 19.7611 16.951 19.5103 16.4265 19.1868C15.8991 18.8617 15.2588 18.3814 14.4614 17.7833L14.4613 17.7833L14.4612 17.7832L14.43 17.7598L11.7502 15.75L9.75 15.75L9.75 19.5001C9.75 20.7427 8.74264 21.7501 7.5 21.7501C6.25736 21.7501 5.25 20.7427 5.25 19.5001L5.25 15.6911C2.98301 15.3315 1.25 13.3681 1.25 11C1.25 8.37663 3.37665 6.24998 6 6.24998L11.7498 6.24998L14.43 4.23981L14.4612 4.2164C15.2587 3.61828 15.8991 3.13799 16.4265 2.81278C16.951 2.48934 17.4859 2.23853 18.0572 2.25041C18.8824 2.26757 19.6563 2.65452 20.1652 3.30444C20.5175 3.75436 20.6378 4.33271 20.6937 4.94641Z" fill="currentColor"></path></svg>&nbsp;&nbsp;&nbsp;<div><b>' + data.message + '</b><p>' + data.subtitle + '</p></div>');
			} else if (data.code == 4) {
				Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.success+'</div>'});
				$('#boost_'+post_id).html('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="feather"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.33 5.43981C16.1659 4.81287 16.7517 4.37451 17.2138 4.08957C17.6856 3.79863 17.9076 3.74762 18.026 3.75008C18.4011 3.75788 18.7529 3.93377 18.9842 4.22919C19.0572 4.32242 19.1496 4.53062 19.1999 5.08266C19.2492 5.62331 19.25 6.35492 19.25 7.39981L19.25 7.99985L19.25 13.9998L19.25 14.5998C19.25 15.6447 19.2492 16.3763 19.1999 16.917C19.1496 17.469 19.0572 17.6772 18.9842 17.7704C18.7529 18.0659 18.4011 18.2417 18.026 18.2495C17.9076 18.252 17.6856 18.201 17.2138 17.9101C16.7517 17.6251 16.1659 17.1868 15.33 16.5598L12.75 14.6248L12.75 7.37481L15.33 5.43981ZM20.6937 4.94641C20.7493 5.55603 20.75 6.34469 20.75 7.32486C22.4617 7.67232 23.75 9.18563 23.75 10.9998C23.75 12.8141 22.4617 14.3274 20.75 14.6748C20.75 15.655 20.7493 16.4436 20.6937 17.0532C20.6378 17.6669 20.5175 18.2453 20.1652 18.6952C19.6563 19.3451 18.8824 19.7321 18.0572 19.7492C17.4859 19.7611 16.951 19.5103 16.4265 19.1868C15.8991 18.8617 15.2588 18.3814 14.4614 17.7833L14.4613 17.7833L14.4612 17.7832L14.43 17.7598L11.7502 15.75L9.75 15.75L9.75 19.5001C9.75 20.7427 8.74264 21.7501 7.5 21.7501C6.25736 21.7501 5.25 20.7427 5.25 19.5001L5.25 15.6911C2.98301 15.3315 1.25 13.3681 1.25 11C1.25 8.37663 3.37665 6.24998 6 6.24998L11.7498 6.24998L14.43 4.23981L14.4612 4.2164C15.2587 3.61828 15.8991 3.13799 16.4265 2.81278C16.951 2.48934 17.4859 2.23853 18.0572 2.25041C18.8824 2.26757 19.6563 2.65452 20.1652 3.30444C20.5175 3.75436 20.6378 4.33271 20.6937 4.94641Z" fill="currentColor"></path></svg>&nbsp;&nbsp;&nbsp;<div><b>' + data.message + '</b><p>' + data.subtitle + '</p></div>');
			}
		}
	});
}

$(document).on('click','.delete--comment--reply',function(event) {
	var zis = $(this);
	var id  = zis.parents('.confirm--modal').data('id');
	if (id) {
		zis.parents('.confirm--modal').data('id',"").fadeOut(300, function() {
			$("[data-post-comment-reply='"+id+"']").slideUp(300,function(){
				$(this).remove();
			});
			$.post(link('comments/delete_reply'), {reply_id:id});
			$('.confirm--modal').css('display', 'none');
		});;
	}
});

function comment_status(post_id){
	if (not(is_logged())) {
		redirect('home');
		return false;
	}
	$.post(link('posts/on_comments'), {post_id: post_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			if (data.code == 1) {
				$('.add-comment .form-group input').attr('disabled', true);
				$('#comments_stats_'+post_id).html('<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" class="feather"><path fill="currentColor" d="M20,2H4C2.897,2,2,2.897,2,4v18l4-4h14c1.103,0,2-0.897,2-2V4C22,2.897,21.103,2,20,2z M9,12c-1.104,0-2-0.896-2-2 s0.896-2,2-2s2,0.896,2,2S10.104,12,9,12z M15,12c-1.104,0-2-0.896-2-2s0.896-2,2-2s2,0.896,2,2S16.104,12,15,12z"></path></svg>&nbsp;&nbsp;&nbsp;<div><b>' + data.message + '</b><p>' + data.subtitle + '</p></div>');
				Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.success+'</div>'});
			}else{
				$('.add-comment .form-group input').removeAttr('disabled');
				$('#comments_stats_'+post_id).html('<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" class="feather"><path fill="currentColor" d="M20,2H4C2.897,2,2,2.897,2,4v18l4-4h14c1.103,0,2-0.897,2-2V4C22,2.897,21.103,2,20,2z M16.706,13.543l-1.414,1.414 l-3.293-3.292l-3.292,3.292l-1.414-1.414l3.292-3.292L7.293,6.958l1.414-1.414l3.292,3.292l3.293-3.292l1.414,1.414l-3.292,3.293 L16.706,13.543z"></path></svg>&nbsp;&nbsp;&nbsp;<div><b>' + data.message + '</b><p>' + data.subtitle + '</p></div>');
				Snackbar.show({showAction: false,backgroundColor: '#fff',text: '<div class="snack-div">'+data.success+'</div>'});
			}
		}
	});
}

function clickAndDisable(link) {
    link.className += " disabled";
    link.onclick = function(event) {
        event.preventDefault();
    }
}

$( document ).on( 'click', '.cr-plans label', function(){
	$('.payment').fadeIn(400).removeClass('hidden');
});

function Price() {
	var plans = document.getElementsByName('plans');
	for (index=0; index < plans.length; index++) {
		if (plans[index].checked) {
			return plans[index].getAttribute('data-price');
			break;
		}
	}
}
	
function description() {
	var plans = document.getElementsByName('plans');
	for (index=0; index < plans.length; index++) {
		if (plans[index].checked) {
			return plans[index].value;
			break;
		}
	}
}

function InputEmoji(id, code, type) {
    inputPad = $('[id=vote-postf-'+id+']').find('.comment');
	if (type == 'lightbox') {
		inputPad = $('[id=section-'+id+']').find('.comment_light');
	} else if (type == 'view-post') {
		inputPad = $('[id=section-'+id+']').find('.comment');
	} else if (type == 'view-tile') {
		inputPad = $('[id=reply-'+id+']').find('.comment');
	} else if (type == 'view-reply') {
		inputPad = $('[id=reply-'+id+']').find('.comment');
	} else if (type == 'main-post') {
		inputPad = $('[id=index-'+id+']').find('.comment');
	}
    inputVal = inputPad.val();
    if (typeof(inputPad.attr('placeholder')) != "undefined") {
        inputPlaceholder = inputPad.attr('placeholder');
        if (inputPlaceholder == inputVal) {
            inputPad.val('');
            inputVal = inputPad.val();
        }
    }
    if (inputVal.length == 0) {
        inputPad.val(code + '');
    } else {
        inputPad.val(inputVal + ' ' + code);
    }
    inputPad.keyup().focus();
}

function EnterEmoji(code, type) {
    inputPad = '';
	if (type == 'message') {
		inputPad = $('#mgs-text-input');
	} else if (type == 'caption') {
		inputPad = $('.upload-area');
	} else if (type == 'taption') {
		inputPad = $('.tap-area');
	} else if (type == 'post-cap') {
		inputPad = $('.px-post');
	}
    inputVal = inputPad.val();
    if (typeof(inputPad.attr('placeholder')) != "undefined") {
        inputPlaceholder = inputPad.attr('placeholder');
        if (inputPlaceholder == inputVal) {
            inputPad.val('');
            inputVal = inputPad.val();
        }
    }
    if (inputVal.length == 0) {
        inputPad.val(code + '');
    } else {
        inputPad.val(inputVal + ' ' + code);
    }
    inputPad.keyup().focus();
}