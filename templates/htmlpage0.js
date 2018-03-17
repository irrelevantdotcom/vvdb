// initial load of page from web
jQuery(document).ready(function($){

	var altlist = document.getElementById('altlist');
	var contentlist = document.getElementById('contentlist');
	var myli = altlist.getElementsByTagName('li')[0];
	var canonbase = myli.getAttribute('canonbase');
	fetch(canonbase + ".json")
		.then((resp) => resp.json())
		.then(function(data) {
			update_altlist(altlist, contentlist, data.alternatives, canonbase);

			timeline_init($);
		});
});

/**
 * Refresh timeline.

 	called on first load of page.
	.. or on selecting a different page ?

 	altlist - link to timeline 'ol'
 	alternates - array of page information
 	curdate - date of item to be left selected
 *
 * @access public
 * @return void
 **/
function update_altlist(altlist, contentlist, alternates, canonbase) {


	// grab the first entry in the lists
	var mytli = altlist.getElementsByTagName('li')[0].cloneNode(true);
	var mycli = contentlist.getElementsByTagName('li')[0].cloneNode(true);

	console.log (altlist, canonbase);


	// empty list
	var cNode = altlist.cloneNode(false);
	altlist.parentNode.replaceChild(cNode, altlist);
	var cNode = contentlist.cloneNode(false);
	contentlist.parentNode.replaceChild(cNode, contentlist);

	// refresh pointers
	var altlist = document.getElementById('altlist');
	var contentlist = document.getElementById('contentlist');

//	console.log (altlist, mytli);

	// now populate the lists
	alternates.map(
		function(alt) {
			// make a copy of the timeline entry and content entry templats.
			var tli = mytli.cloneNode(true);
			var cli = mycli.cloneNode(true);


			// update timeline entry
			var a = tli.getElementsByTagName('a')[0];

		 	var d = new Date(alt.varient_date);
		 	a.innerHTML = d.toDateString().slice(-11);
		 	let datestring = ("0" + d.getDate()).slice(-2) + "/" + ("0"+(d.getMonth()+1)).slice(-2) + "/" +
  d.getFullYear() + "T" + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
			a.setAttribute('data-date',datestring);

			tli.setAttribute('canonbase', alt.canonbase);

			// make sure 'current' date is selected.
			if (alt.canonbase == canonbase) {
				a.setAttribute('class', 'selected');
				cli.setAttribute('class', 'selected');
			} else {
				a.removeAttribute('class');
				cli.removeAttribute('class');
			}

			// update content entry
			cli.setAttribute('data-date',datestring);
			cli.setAttribute('canonbase', alt.canonbase);


			cli.getElementsByTagName('h1')[0].innerHTML =
				"Page " + alt.frame_id + alt.subframe_id + " " + d.toDateString();
// TODO really need these in a lazyload to stop multiple fetches.
			cli.getElementsByTagName('img')[0].setAttribute('src',alt.canonbase + ".img");

/*			fetch(alt.canonbase + ".tf")
				.then((resp) => resp.text())
				.then(function(data) {
					$('.tflink', '#'+frameunique).attr('href','http://edit.tf/#'+data);
				 });
			// TODO more !
*/
			console.log (tli, cli);

			altlist.appendChild(tli);
			contentlist.appendChild(cli);

			console.log (altlist);
		}
	);
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