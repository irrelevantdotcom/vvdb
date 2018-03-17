// initial load of page from web

// TODO - configure "More info" fields somewhere.

jQuery(document).ready(function($){

	var altlist = document.getElementById('altlist');

	bLazy = new Blazy();

	// Populate timeline from list of alternates

	if (!altlist.classList.contains('altlistdone')) {
		var contentlist = document.querySelector('.contentlist');


		// find first (and only) entry and note as item to copy.
		var myli = altlist.getElementsByTagName('li')[0];
		// get base url for page.
		var canonbase = myli.dataset.canonbase;
		// fetch data about this page
		fetch(canonbase + ".json")
			.then((resp) => resp.json())
			.then(function(data) {
				// create timeline with alternates, and construct content for each
				update_altlist(altlist, contentlist, data.alternatives, canonbase);
				// initialise timeline.
				timeline_init($);
				// initialise subframe list on selected page.
				// Do it this way rther than call blsuccess as we already have
				// the json in 'data' and don't need to get it again.
				var initialsfl = document.querySelector('.events-content')
				 .querySelector('.selected').querySelector('.subpagelist');
				process_subpages(initialsfl, data);
			}).catch(function(err ) {
				console.log ("Oh boy.." + err.message);
				console.log (err.stack);
			});
		altlist.classList.add('altlistdone');
	}
});


/**
 * This constructs the lists that constitute the timeline and
 * the page content for each alternate
 **/
function update_altlist(altlist, contentlist, alternates, canonbase) {
console.log("update_altlist");

	// grab the first entry in the lists
	var mytli = altlist.getElementsByTagName('li')[0].cloneNode(true);
	var mycli = contentlist.getElementsByTagName('li')[0].cloneNode(true);

	// empty list by replacing content with empty clone.
	var cNode = altlist.cloneNode(false);
	altlist.parentNode.replaceChild(cNode, altlist);
	var cNode = contentlist.cloneNode(false);
	contentlist.parentNode.replaceChild(cNode, contentlist);

	// refresh pointers as they still pointed to old lists ..
	var altlist = document.getElementById('altlist');
	var contentlist = document.querySelector('.contentlist');

	// now populate the lists
	alternates.map(
		function(alt) {
			// make a copy of the timeline entry and content entry templats.
			var tli = mytli.cloneNode(true);
			var cli = mycli.cloneNode(true);

			// TODO - make this less dependent on tag order.
			// update timeline entry
			var a = tli.getElementsByTagName('a')[0];

			// Construct and save date.
		 	var d = new Date(alt.varient_date);
		 	a.innerHTML = d.toDateString().slice(-11);
		 	let datestring = ("0" + d.getDate()).slice(-2) + "/" + ("0"+(d.getMonth()+1)).slice(-2) + "/" +
  d.getFullYear() + "T" + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
			a.dataset.date = datestring;

			// set base address for this particular item.
			tli.dataset.canonbase = alt.canonbase;

			// make sure the entry for the 'current' date is selected.
			if (alt.canonbase == canonbase) {
				a.classList.add('selected');
				cli.classList.add('selected');
			} else {
				a.classList.remove('selected');
				cli.classList.remove('selected');
			}

			// update content entry
			cli.dataset.date = datestring;
			cli.dataset.canonbase = alt.canonbase;

			cli.querySelector('.i_date').innerHTML = d.toDateString();
			cli.querySelector('.i_datename').innerHTML = alt.varient_name;
			cli.querySelector('.i_originator').innerHTML = alt.displayname;
			cli.querySelector('.i_authenticity').innerHTML = (alt.auth_description === null ? '' : alt.auth_description);

///			cli.querySelector('.i_pagename').innerHTML = alt.frame_id + alt.subframe_id;
			cli.querySelector('.i_page').innerHTML = alt.frame_id;
			cli.querySelector('.i_subpage').innerHTML = alt.subframe_id;
//			cli.querySelector('.i_description').innerHTML = (alt.description == null ? '' : alt.description);

			// bloody browser crap
//			cli.querySelectorAll('.tflink').forEach( function(f) {
			Array.from(cli.querySelectorAll('.tflink')).forEach( function(f) {
				f.href = f.href.concat(alt.tf);
				});

			// set image so it shows immediately (sort of, it's lazy loaded, but quicker than
			// waiting for the ajax to complete when switching to a new alternate.)
			cli.querySelector('.mainimage').dataset.src = alt.canonbase + ".img";
			cli.querySelector('.mainimage').setAttribute('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

			// add the new constructs to the timeline and the main content list.
			altlist.appendChild(tli);
			contentlist.appendChild(cli);

//console.log (altlist);
		}
	);
	// as we added new lazy images, need to initialise them.
    bLazy.revalidate();
}

/**
 *  This is called when switching to an alternate from the timeline.
 *  Checks it was main image it was called on, and runs additional
 *  code to fetch rest of data for that content section.
 *  (was supposed to be called from the bLazy 'success' on loading
 *  the image, but now called manually from within main.js's
 *  'updateVisibleContent' function.)
 **/
function blsuccess(ele){
console.log("blsuccess");
	var el = upTo(ele, 'li');
	// find subframe list
	if (!ele.classList.contains('b-loaded')) {
// load it. force it  now so it is loading/loaded by the time the scroll-in happens.
    	bLazy.load(ele, true);
// and load subframes.
		if (ele.classList.contains('mainimage')) {
//			var el = upTo(ele, 'li');
			var sfl = el.querySelector('.subpagelist');
			if (sfl) {
				load_subframelist(sfl);
			}// if
		}
	}
	var url = el.dataset.canonbase + '.html';
	window.history.replaceState({}, "", url);

//	document.querySelector("[property=og:title]").content = ;
// TODO	document.getElementsByTagName('title')[0].textContent = ;

}
/*
 *	Make ajax call to collect all data about a page,
 *  and then call fn to construct list of subframes!
*/
function load_subframelist(subsdiv){
console.log("load_subframelist");

	if (subsdiv.tagName != "DIV") {
		subsdiv = upTo(subsdiv, "div");
	}
	// just double check we've not done this already..
	if (!subsdiv.classList.contains('subloaddone')) {
		var li = upTo(subsdiv, 'li');
		var canonbase = li.dataset.canonbase;

		fetch(canonbase + ".json")
		.then((resp) => resp.json())
		.then(function(data) {

			process_subpages(subsdiv, data);

		});
	} // if
} // fn

/*
 *  Create the list of subframes.
 *  TODO - ensure each option has data-<stuff> that relates to that
 *  subframe!
*/


function process_subpages(subsdiv, data){
//console.log("Process subpages on", subsdiv, subsdiv.parentNode);
		var subpages = data.subpages;
		subpages.map(
		    function(subpage) {
				let img = document.createElement('img'),
					br = document.createElement('br');

				// Image attributes.
//				img.setAttribute('data-src', subpage.canonbase+'_120x100.img');
				img.setAttribute('src', subpage.canonbase+'_120x100.img');
				img.setAttribute('width', 120);
				img.setAttribute('height', 121);
				img.setAttribute('hspace', 10);
				img.setAttribute('alt', subpage.frame_id + subpage.subframe_id);
//				img.classList.add('b-lazy');

				img.dataset.page = subpage.frame_id;
				img.dataset.subpage = subpage.subframe_id;
				img.dataset.description = subpage.description;
				img.dataset.tf = subpage.tf;
				img.dataset.canonbase = subpage.canonbase;
				img.setAttribute('onclick','return imgswap(this);');

				// add to list.
	      		subsdiv.appendChild(img);
	      		subsdiv.appendChild(br);
			} // fn
		); // map
//   		bLazy.revalidate();	// add lazyload triggers to just-added images
		subsdiv.classList.add('subloaddone');

} //fn

/**
 * called on click on subframe image.
 * swap image (TODO and associated data) into main image.
 **/
function imgswap(e){
console.log('imgswap called on ', e);

	var li = upTo(e, 'li');
	li.querySelector('.mainimage').src = e.dataset.canonbase + '.img';

	window.history.replaceState({}, "", e.dataset.canonbase + '.html');

	//TODO - any more?
	li.querySelector('.i_page').innerHTML = e.dataset.page;
	li.querySelector('.i_subpage').innerHTML = e.dataset.subpage;
	li.querySelector('.i_description').innerHTML = e.dataset.description;
	Array.from(li.querySelectorAll('.tflink')).forEach(function(f){
						f.href = f.href.replace('/\#.*/i', '#' + e.dataset.tf);
				});

	// these probably don't really need doing, as will be set on initial page creation
	// and thus checking any particular url by (e.g.) facebook will have correct values
	// already in them.
//	document.querySelector("[property=og:url]").content = url;
//	document.querySelector("[property=og:image]").content = e.href;

	return false;	// false to disable normal operation of click, i.e. following of link!
}



//https://stackoverflow.com/questions/6856871/getting-the-parent-div-of-element
// Find first ancestor of el with tagName
// or undefined if not found
function upTo(el, tagName) {
  tagName = tagName.toLowerCase();

  while (el && el.parentNode) {
    el = el.parentNode;
    if (el.tagName && el.tagName.toLowerCase() == tagName) {
      return el;
    }
  }
  return null;
}


// from https://gist.github.com/excalq/2961415
// with bigfix by Rob. (replace 'param' with 'key' in passed params!)
// used when clicking tabs to make sure current tab is remembered when coming back to this page.
function updateQueryStringParam(key, value) {
  baseUrl = [location.protocol, '//', location.host, location.pathname].join('');
  urlQueryString = document.location.search;
  var newParam = key + '=' + value,
  params = '?' + newParam;

  // If the "search" string exists, then build params from it
  if (urlQueryString) {
    keyRegex = new RegExp('([\?&])' + key + '[^&]*');
    // If param exists already, update it
    if (urlQueryString.match(keyRegex) !== null) {
      params = urlQueryString.replace(keyRegex, "$1" + newParam);
    } else { // Otherwise, add it to end of query string
      params = urlQueryString + '&' + newParam;
    }
  }
  window.history.replaceState({}, "", baseUrl + params);
}

/**
 *
 * @access public
 * @return void
 **/
function updateCanonbase(canonbase){
  baseUrl = [location.protocol, '//', location.host].join('');

window.history.replaceState({}, "", baseUrl + params + canonbase + '.html');

}




/*
function update_content(myli, loaddate, canonbase){

	fetch(canonbase + ".json")
		.then((resp) => resp.json())
		.then(function(data) {

			let subpages = data.subpages,
				alternates = data.alternatives,
				mydate = new Date("{$date|date_format:"%B %d, %Y %H:%M:%S"}");

				altlist = li.document.getElementById('altlist');

	let ret = subpages.map(
	    function(subpage) {
			let li = document.createElement('li'),
				img = document.createElement('img'),
				span = document.createElement('span'),
				a = document.createElement('a');
			img.src = subpage.canonbase+'_120x100.img';
			span.innerHTML = subpage.frame_id + subpage.subframe_id;
			a.href = subpage.canonbase + '.html';
			a.appendChild(img);
      		a.appendChild(span);
      		li.appendChild(a);
      		document.getElementById('subpagelist').appendChild(li);
		}
	) +
	);
});

function update_content(frameunique) {
	var cb = data[frameunique];

	fetch(cb + ".tf")-
		.then((resp) => resp.text())
		.then(function(data) {
			$('.tflink', '#'+frameunique).attr('href','http://edit.tf/#'+data);
		 });

		 	var data = '';

	fetch (cb + ".json")
		.then((resp) -> resp.json())
		.then (function(data) {
		let subpages = data.subpages,
			alternates = data.alternatives,
			myli = document.getElementById('altli'),
			mydate = new Date("{$date|date_format:"%B %d, %Y %H:%M:%S"}");
			altlist = document.getElementById('altlist');


</script>

}

</script>

*/