"use strict";

/* TO-DO:
Link the news-selector to image, so when user hovers mouse over topic the image changes.
The page does not resize correctly.
The video selection is not pretty. You might want to improve it.
Translations
You need to split this file into multiples.
REFACTOR
*/

/* Set cookie with certain name, value and expiration date*/
function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
/* Get cookie with certain name */
function getCookie(cname) {
	var name = cname + "=";

	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(";");
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length)
		}
	}

	return "";
}

function deleteCookie(name) {
	document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;';
}

function changePrivileges() {
	var roles = [
		$("#admin").prop('checked'),
		$("#editor").prop('checked'),
		$("#blogger").prop('checked')
	];

	if ($('input[name=action]:checked').val() == "") {
		alert("You need to select action");
	}

	try {
		$.post(
			'main/php/changePrivileges.php',
			{username:getCookie("username"),
			account:$("#username").val(),
			action:$('input[name=action]:checked').val(),
			roles},
			function(response, status){
				if(status == 'success') {
					if(response == "success"){
						$("#statusUpload").show();
						console.log('Change: ', response);
					} else {
						console.log('Change: ', response);
					}
				} else {
					alert("jQuery post failed: ", status, response);
				}
			});
	} catch (err) {
		console.log("Change: ", err);
	}
}

function reloadAccount() {
	var username = getCookie("username");
	if (username != "") {
		$('#account').html(username);
		$('#loginButton').hide();
		document.getElementById('accountDrop').style.display= 'inline-block';
	}
}

function getLocalization(type, language, callback) {
	try {
		$.getJSON("main/data/" + type + "-" + language + ".json", function(localization, status){
			if (status == "success") {
				console.log("Localization: success");
				callback(localization);
				return localization;
			} else {
				console.log("Localization: " + status + data);
			}
		});
	} catch (err) {
		console.log("Localization: ", err.message);
	}
	return "";
}

function setLocalization(language) {
	deleteCookie("language");
	setCookie("language", language, 365);
	loadSite();
}

function getWords(str) {
	return str.split(/\s+/).slice(0,50).join(" ");
}

function enlargeVideo(id) {
	var video_width = $("div.videos").width();
	document.getElementById('videos').innerHTML = "<iframe id=" + id + " height=" + (video_width/16)*9 + " width=" + video_width + " src=https://www.youtube.com/embed/" + id + " alt='video' frameborder='0' allowfullscreen />";
	document.getElementById('video_close').style.display = "inline-block";
	console.log("Enlarge: Success");
}

function getNews(localization) {
	var text = "";
	var lang = getCookie("language");
	$('#news-selector').html("");
	try {
		for(var i = 0; i < 5; i++) {
			text = "<a href='articles.html?type=news&id=" + i + "&lang=" + lang + "'>" + localization.news[i].title + "</a>";
			console.log("News: Success");
		}
		$('#news-image').attr("src", localization.news[0].thumbnail);
	} catch (err) {
		console.log("News: ", err.message);
	}
	$('#news-selector').html(text);
}

function getVideos(localization) {
	$('#videos').html("");
	try {
		var width = $("div.videos").width();
		width = width / 5;
		for(var i = 0; i < 5; i++) {
			var thumbnail = localization.videos[i].thumbnail;
			var text = "<div style='display:inline-block;max-width:"+ width +"px'>";
			text += "<img id=" + thumbnail + " height=" + width + "px width=" + width + "px src=https://img.youtube.com/vi/" + thumbnail + "/0.jpg" + " alt='thumbnail' onclick='enlargeVideo(this.id)' />";
			text += "<h3 style='white-space:normal'>" + localization.videos[i].title + "</h3>";
			text += "</div>";
			$('#videos').append(text);
			console.log("Videos: Success");
		}
	} catch (err) {
		console.log("Videos: ", err.message);
	}
}

function getOpinions(localization) {
	$('#opinions').html("");
	try {
		var width = $("div.opinions").width();
		width = width / 5;
		for(var i = 0; i < 5; i++) {
			var text = "<div style='display:inline-block;width:" + width + "px'>";
			text += "<a style='background-color:transparent' href='articles.html?type=opinions&id=" + i +"'><img height=" + width + "px width=" + width + "px src=" + localization.opinions[i].thumbnail +" alt='thumbnail' /></a>";
			text += "<h3>" + localization.opinions[i].title + "</h3>";
			text += "</div>";
			$('#opinions').append(text);
			console.log("Opinions: Success");
		}
	} catch (err) {
		console.log("Opinions: ", err.message);
	}
}

function getHeader(localization, callback) {
	var language = getCookie("language");
	var element = "<img height=20 src='main/data/img/nordic_media24.png' />" +
		"<h2 id='header'><a href='index.html'>Nordic Media 24</a></h2>" +
		"<div class='dropdown'>" +
			"<a href='articles.html?type=news'>" + localization.header.news.news + "</a>" +
			"<div class='dropdown-content'>" +
				"<a href='articles.html?type=news'>Technology</a>" +
				"<a href='articles.html?type=news'>Finland</a>" +
				"<a href='articles.html?type=news'>Somalia</a>" +
				"<a href='articles.html?type=news'>Diaspora</a>" +
			"</div>" +
		"</div>" +
		"<a href='articles.html?type=videos'>" + localization.header.videos + "</a>" +
		"<a href='articles.html?type=opinions'>" + localization.header.opinions + "</a>" +
		"<div class='dropdown'>" +
			"<a href='about.html?type=news'>" + localization.header.about + "</a>" +
			"<div class='dropdown-content'>" +
				"<a href='articles.html?type=about'>Blog</a>" +
			"</div>" +
		"</div>" +
		"<a id='loginButton' onclick=" + "$('#login').show();" + ">Log in</a>" +
		"<div id='accountDrop' class='dropdown'>" +
			"<a id='account' href='account.html'>Account</a>" +
			"<div class='dropdown-content'>" +
				"<a id='logout' onclick=" + "deleteCookie('username')" + ">Log out</a>" +
				"<a href='publish.html'>Publish</a>" +
				"<a href='manager.html'>Manager</a>" +
			"</div>" +
		"</div>" +
		"<select id='language' onchange='setLocalization(this.value)'>" +
			"<option value='english'>english</option>" +
			"<option value='finnish'>Suomi</option>" +
			"<option value='somali'>Somali</option>" +
		"</select>";
	document.getElementById("nav").innerHTML = element;
	callback();
}

function getMember(member) {
	var name = "";
	if (member["full_name"]) {
		name = member["full_name"];
	}
	var width = $("#team").width();
	width = width/5;
	var text = "<div style='display:inline-block;max-width:" + width + "px'>";
	text += "<img src=" + member["image"] +" alt='No image found' />";
	text += "<h2>name: " + name + "</h2>";
	text += "<h2>email: " + member["email"] + "</h2>";
	if (member["admin"] == 1) {
		text += "<h2>Role: Admin</h2>";
	} else if (member["editor"] == 1) {
		text += "<h2>Role: Editor</h2>";
	} else if (member["blogger"] == 1) {
		text += "<h2>Role: Blogger</h2>";
	}
	text += "</div>";
	return text;
}

function getTeam(localization) {
	try {
		$.post(
			'main/php/getTeam.php',
			function(response, status){
				if(status == 'success') {
					var text = "";
					for (var i = 0; i < response.length; i++) {
						text += getMember(response[i]);
					}
					$('#team').html(text);
				} else {
					alert("jQuery post failed: ", status, response);
				}
			});
	} catch (err) {
		console.log("Team: ", err);
	}
}

function loadArticles(localization) {
	var searchParams = new URLSearchParams(window.location.search);
	var type = searchParams.get("type"); // get type from url
	var i = searchParams.get("id"); // get id from url
	var iNull = false;
	var looper = 1;

	$('#articles').html("");
	if (i == null) {
		iNull = true;
		i = 0;
		looper = 5
	}
	var thing;
	for (var key in localization) {
		if (type == key) {
			thing = localization[type];
		}
	}

	if (thing == null) {
		return 0;
	}

	for (; i < looper; i++) {
		var text = "";
		text = "<h1>" + thing[i].title + "</h1>";
		text += "<h2>" + thing[i].subtitle + "</h2>";
		if (iNull) {
			text += "<p>" + getWords(thing[i].main_text) + "</p>";
		} else {
			text += "<p>" + thing[i].main_text + "</p>";
		}
		if (type == "videos") {
			var video_width = $("#articles").width();
			var id = thing[i].thumbnail;
			text += "<iframe id=" + id + " height=" + (video_width/16)*9 + " width=" + video_width + " src=https://www.youtube.com/embed/" + id + " alt='video' frameborder='0' allowfullscreen /></div>";
		}

		text = text.replace(/\r?\n/g, '<br />');
		$('#articles').append(text);
	}
}

function deleteItem(language, key, index) {
	if (confirm("You're about to delete an item. Are you sure?") == false) {
		return 0;
	}
	try {
		$.post(
			'main/php/deleteArticle.php',
			{username:getCookie("username"), "language":language, "key":key, "index":index},
			function(response, status){
				if(status == 'success') {
					if (response == "success") {
						alert("Item deleted successfully");
						loadSite();
					} else {
						console.log("Delete: ", response);
					}
				} else {
					alert("jQuery post failed: ", status, response);
				}
			});
	} catch (err) {
		console.log("Team: ", err);
	}
	return 1;
}

function managerItem(id, language, key, localization) {
	var username = getCookie("username");
	for (var i = 0; i < localization[key].length; i++) {
		if (localization[key][i].username == username) {
			$('#' + id).append("<div style='display:inline-block;border: solid 1px black'>" +
			"<h2 >" + localization[key][i].title + "</h2>" +
			"<h3 class='manager' onclick='deleteItem(\"" + language + "\", \"" + key + "\", " + i + ")'>Delete?</h3>" +
			"</div>");
		}
	}
}

function manager(language, localization) {
	for (var key in localization) {
		if (key == "videos") {
			managerItem("your_videos", language, key, localization);
		} else {
			managerItem("your_articles", language, key, localization);
		}
	}
}

function loadManager(localization) {
	$('#your_articles').html("");
	$('#your_videos').html("");
	$('#language option').each(function(index){
		var language = this.value;
		getLocalization("data", language, function(localization){
			manager(language, localization);
		});
	});
}

function closeSignUp() {
	$('#createUser').show();
	$('#create').hide();
	$('#closeSignUp').hide();
	$('.email').hide();
	$('#email').prop('required', false);
	$('#psw1').hide();
	$('#psw1').prop('required', false);
	$('#submitLogin').show();
}

function signUp() {
	$('#createUser').hide();
	$('#create').show();
	$('#closeSignUp').show();
	$('.email').show();
	$('#email').prop('required', true);
	$('#psw1').show();
	$('#psw1').prop('required', true);
	$('#submitLogin').hide();
}

function getLogin(localization) {
	var element = "<div class='modal-content animate'>" +
			"<div class='imgcontainer'>" +
				"<span onclick=" + "document.getElementById('login').style.display='none'" + " class='close' title='Close login'>&times;</span>" +
				"<img src='main/data/img/nordic_media24.jpg' alt='Avatar' class='avatar' />" +
			"</div>" +
			"<div class='container'>" +
				"<p id='loginMessage'></p>" +
				"<label><b>Username</b></label>" +
				"<input id='uname' type='text' placeholder='Enter username' required />" +
				"<label><b>Password</b></label>" +
				"<input id='psw0' type='password' placeholder='Enter password' required />" +
				"<input id='psw1' type='password' placeholder='Enter password' />" +
				"<div class='email'>" +
				"<label><b>Email</b></label>" +
				"<input id='email' type='email' placeholder='Enter email' required />" +
				"</div>" +
				"<button id='submitLogin' type='submit' onclick='login()'>Login</button>" +
				'<button id="createUser" onclick="signUp()">Create User</button>' +
				'<button id="create" onclick="createUser()" type="submit">Create</button>' +
				'<button id="closeSignUp" onclick="closeSignUp()">Login</button>' +
				'<span><input type="checkbox" /> Remember me (doesn\'t actually work)</span>' +
				'<span class="psw">Forgot <a href="#">password?</a></span>' +
			'</div></div>';
	$("#login").html(element);
}

function loadData(localization) {
	try {
		loadManager(localization);
	} catch (e) {
		console.log(e);
	}
	try {
		getTeam(localization);
	} catch (e) {
		console.log(e);
	}
	try {
		loadArticles(localization);
	} catch (e) {
		console.log(e);
	}
	getNews(localization);
	getVideos(localization);
	getOpinions(localization);
}

function loadFrame(localization) {
	var language = getCookie("language");
	getHeader(localization, function(){
		$("#language").val(language);
	});
	reloadAccount();
	getLogin(localization);
}

function loadSite() {
	var searchParams = new URLSearchParams(window.location.search);
	var language = searchParams.get("lang"); // get type from url
	if (language == null) {
		language = getCookie("language");
		if (language == null) {
			language = "english";
		}
	}
	getLocalization("localization", language, function(localization){
		loadFrame(localization);
	});
	getLocalization("data", language, function(localization){
		loadData(localization);
	});
	$('#video_close').hide();
}

function checkInput(input) {
	for (var i = 0; i < input.length; i++) {
		if (input[i] == "") {
			return false;
		}
	}
	return true;
}

function createUser(){
	var uname = document.getElementById('uname').value;
	var pword0 = document.getElementById('psw0').value;
	var pword1 = document.getElementById('psw1').value;
	var email = document.getElementById('email').value;
	var input = [uname, pword0, pword1, email];
	if (!checkInput(input)) {
		alert("Fill all input fields");
		return 0;
	}

	if (pword0 == pword1) {
		try {
			$.post(
				'main/php/createUser.php',
				{username:uname, password:pword0, "email":email},
				function(response, status){
					if(status == 'success') {
						if (response == "success") {
							$("#loginMessage").show();
							$("#loginMessage").html("User created, you can log in now.");
							console.log('Login: success');
						} else {
							$("#loginMessage").show();
							console.log(response);
							$("#loginMessage").html(response);
						}
					} else {
						alert("jQuery post failed: ", status, response);
					}
				});
		} catch (err) {
			console.log("Login: ", err);
		}
	} else {
		alert("Passwords don't match!")
	}
}

function login() {
	var uname = document.getElementById('uname').value;
	var pword = document.getElementById('psw0').value;
	var input = [uname, pword];
	if (!checkInput(input)) {
		alert("Fill all input fields");
		return 0;
	}

	try {
		$.post(
			'main/php/login.php',
			{username:uname, password:pword},
			function(response, status){
				if(status == 'success') {
					if (response["login"] == "success") {
						setCookie("username", uname, 365);
						if (response["editor"] == 1) {
							setCookie("editor", true, 365);
						}
						reloadAccount();
						$("#login").hide();
					} else {
						alert("Username or password was wrong");
					}
				} else {
					alert("jQuery post failed: ", status, response);
				}
			});
	} catch (err) {
		console.log("Login: ", err);
	}
}

function addLink(){
	var result = prompt("Enter source", "null");
	document.getElementById("main_text").value += "<a href='" + result + "'>REPLACE THIS</a>";
}

function addImage(){
	var result = prompt("Enter source", "null");
	document.getElementById("main_text").value += "<img width=100% src='" + result + "'/>";
}

function preview(){
	var text = "<h1>" + $("#title").val() + "</h1>";
	text += "<h2>" + $("#subtitle").val() + "</h2>";
	text += "<p>" + $("#main_text").val() + "</p>";
	var text = text.replace(/\r?\n/g,  '<br />');
	document.getElementById("preview_area").innerHTML = text;
}

function parseThumbnail() {
	try {
		var r, rx = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/; // Regular expression stuff
		var url = $('#thumbnail').val();

		if ($("input[name=type]:checked").val() == "videos") {
		    r = url.match(rx);
			console.log("parse", r[1]);
			return r[1];
		} else {
			r = url;
		}
		console.log("parse", r);
		return r;
	} catch (e) {
		console.log(e);
	}
	return $('#thumbnail').val();
}

function publish(){
	if (getCookie("username") == "") {
		alert("Log in first");
		return 0;
	}
	var date = new Date();
	var article = {
		"username":getCookie("username"),
		"year":date.getUTCFullYear(),
		"month":date.getUTCMonth(),
		"date":date.getUTCDate(),
		"hour":date.getUTCHours(),
		"thumbnail":parseThumbnail(),
		"title":$("#title").val(),
		"subtitle":$("#subtitle").val(),
		"main_text":function(){
			if ($("input[name=type]:checked").val() == "videos") {
			    return "";
			}
			return $("#main_text").val();
		}
	};
	var data = {
		"type":$("input[name=type]:checked").val(),
		"language":$('select[name="articleLanguage"]').val(),
		"article":article
	};

	try {
		$.post(
			'main/php/saveArticle.php',
			data,
			function(response, status){
				if(status == 'success') {
					if(response == "success"){
						$("#statusUpload").show();
					}
					console.log('Publishing: ', response);
				} else {
					alert("jQuery post failed: ", status, response);
				}
			});
	} catch (err) {
		console.log("Publishing: ", err);
	}
}

$(document).ready(function(){
	// jQuery methods go here...
	try{
		loadSite();
	} catch (err) {
		console.log("Main: ", err.message);
	}
});
