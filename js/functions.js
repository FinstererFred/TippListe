
var proxy = "php/proxy.php";

var teams = {
		"t156"	:"Bayern",
		"t172"	:"Wolfsburg",
		"t159"	:"Frankfurt",
		"t160"	:"Freiburg",
		"t162"	:"Berlin",
		"t171"	:"Bremen",
		"t387"	:"Köln",
		"t161"	:"Hamburg",
		"t808"	:"Hannover",
		"t167"	:"Schalke",
		"t1902"	:"Hoffenheim",
		"t1772"	:"Augsburg",
		"t157"	:"Dortmund",
		"t164"	:"Leverkusen",
		"t1743"	:"Paderborn",
		"t810"	:"Mainz",
		"t683"	:"Gladbach",
		"t169"	:"Stuttgart"};

var tips= {};
var userPunkte = {};
var winAmount = userNames.length;

function getRandom() {
    return Math.random() * (999999 - 100000) + 100000;
}

$(function() {
	/* onclick */
		$('.scrollTop').on('click', function() {
			window.scrollTo(0, 0);
		});

		$('table').on('focus', 'input[type="tel"]', function(event) {
				if($(this).val() == 'x') $(this).val('');
			});


		$('table').on('keyup','input[type="tel"]:nth-child(2)', function(event) {

				var input = $(this).val();

				if($.isNumeric(input)) {
					saveTip( $(this).parent().data('spiel'), $(this).parent().data('eventid'), $(this).parent().parent().data('user'), $(this).closest('table').attr('id').split('round')[1], input, 1);

					$(this).next('input[type="tel"]').focus();

					$(this).next('input[type="tel"]').val( $(this).next('input[type="tel"]').val() );
				} else {
					$(this).val('');
				}

			});

		$('table').on('keyup','input[type="tel"]:nth-child(3)', function(event) {

			var input = $(this).val();

			if($.isNumeric(input)) {
				saveTip( $(this).parent().data('spiel'), $(this).parent().data('eventid'), $(this).parent().parent().data('user'), $(this).closest('table').attr('id').split('round')[1], input, 2);

				$(this).closest('td').next().next().find('input[type="tel"]:first').focus();

				$(this).closest('td').next().next().find('input[type="tel"]:first').val( $(this).closest('td').next().next().find('input[type="tel"]:first').val() );
			} else {
				$(this).val('');
			}
		});

		$('table:gt('+(actRound-1)+')').on('click', '.rotKnopf', function(event) {
			$(this).parent().parent().find('td').each(function () {
				$(this).data('rot', false).removeClass('rot');
			});

			$(this).parent().data('rot', true).addClass('rot');
			saveRot( $(this).parent().data('spiel'), $(this).parent().parent().data('user'), $(this).parent().closest('table').attr('id').split('round')[1] );
		});

		$('td input[type="checkbox"]:enabled').on('change', function() {
			var round = $(this).closest('table').attr('id').split('round')[1];
			var user = $(this).parent().parent().data('user');

			var checked = $(this).prop('checked') ? 1 : 0;

			$.ajax({
			  method: "GET",
			  url: "php/ajax.php",
			  cache: false,
			  data: { action: 'saveBez', round: round, user: user, bezahlt: checked, rnd: getRandom() },
			  dataType: 'json'
			});

		});

		$('.tableHeader').on('click', function() {
			$(this).parent().find('tr:not(.tableHeader):not(.erg)').toggle();
		});

		getTable('tableList');
		getInjured('tableInjured');

		$('.begegnung img').on('mouseover', function() {
			var teamid = $(this).data('teamid');
			$('img.'+teamid).addClass("highTeam");
		});


		$('.begegnung img').on('mouseout', function() {
			var teamid = $(this).data('teamid');
			$('img.'+teamid).removeClass("highTeam");
		});
});


function saveTip(spiel, eventId, userId, round, tipp, typ) {
	$.ajax({
	  method: "GET",
	  url: "php/ajax.php",
	  cache: false,
	  data: { action: 'saveTip', spiel: spiel, user:userId, round:round, tipp:tipp, typ:typ, eventId:eventId, rnd: getRandom() },
	  dataType: 'json'
	})
	.done(function( cartProducts ) {
		;
	});
}


function saveRot(spiel, userId, round) {

	$.ajax({
	  method: "GET",
	  url: "php/ajax.php",
	  cache: false,
	  data: { action: 'saveRot', spiel: spiel, user:userId, round:round, rnd: getRandom() },
	  dataType: 'json'
	})
	.done(function( cartProducts ) {
		;
	});
}


function sumPoints() {
	var that = this;

	for(var j in userPunkte) {
		userPunkte[j]['totalPoints'] = 0;
		userPunkte[j]['wins'] = 0;
	}

	var spieltag = 0;

	$('.punkte').each(function() {
		var userSum = 0;
		var userSpieltag = $(this).closest('table').data('spieltag');


		var spieler = $(this).parent().data('user');

		$(this).parent().find('.matchpoints').each(function() {
			userSum += parseInt($(this).html());
		});

		$(this).html(userSum);

		if (typeof(userPunkte[spieler]) == 'undefined' ) {
			userPunkte[spieler] = {};
		}

		if(typeof(userPunkte[spieler]['totalPoints']) == 'undefined') {
			userPunkte[spieler]['totalPoints'] = 0;
		}

		userPunkte[spieler]['totalPoints'] += userSum;

	});

	for (i = 1; i < actSpieltag; i++) {
		markWinner(i);
	}

	var sortable = [];

	for (var punkt in userPunkte) {
		sortable.push([punkt, userPunkte[punkt]])
	}

	sortable.sort(function(a, b) {return b[1]['totalPoints'] - a[1]['totalPoints']})

	var out = '';
	var p = 1;

	for(var i in sortable) {
		if (typeof(sortable[i][1]['wins']) == 'undefined') {
			sortable[i][1]['wins'] = 0;
		}
		out += '<tr class="pointsTR"><td class="pointsName">'+userNames[ (sortable[i][0]-1)]+'</td><td>'+sortable[i][1]['totalPoints']+'</td><td>'+p+'</td><td>'+sortable[i][1]['wins']+' &euro;</td></tr>';
		p++;
	}

	$('.pointsTR').remove();
	$('#points').append(out);

}

function Spieltag(round) {
    this.round = round;
    this.matches = '';
	this.tips = tips[this.round];
    this.loadMatches(true);
}

Spieltag.prototype = {
	loadMatches : function(writeMatches) {
		/* load data */
        var that = this;
        $.ajax({type: "GET", url: proxy+"?type=round&round="+this.round, cache: false, dataType: "xml"})
         .done(function(xml)  {
        	var doc = $.parseXML(xml);

        	that.Matches = xml;

        	if(writeMatches) {

        		that.writeResults();

    			that.calculatePoints();
        	} else {
        		that.writeResults();

        		that.calculatePoints();
        	}
         });

	},

	writeMatches : function() {
	    var xml = this.Matches;
		var out = '';
		$(xml).find('fixture').each(function() {
            var teamHome = $(this).find("teamHome").attr('teamId');
            var teamAway = $(this).find("teamAway").attr('teamId');

            out+= '<th><img src="img/'+teamHome+'.png" title="'+teams[teamHome]+'"/> <img src="img/'+teamAway+'.png" title="'+teams[teamAway]+'"/></th><th></th>';

        });

         $(out).insertAfter('#round'+this.round+' .spieltag');
	},

	writeResults : function() {
		$('#round'+this.round+' .erg').remove();

		var xml = this.Matches;

		var that = this;

		var out = '<tr class="erg"><td>Erg</td>';

		$('#round'+this.round+' th.begegnung').each(function() {
			var eventId = $(this).data('eventid');

			var Match = $(spieltag.Matches).find('fixture[eventId='+eventId+']');

            var teamHomeScore = $(Match).find("teamHome score").attr('total');
            teamHomeScore = (typeof(teamHomeScore) == 'undefined') ? 'x' : teamHomeScore;

            var teamAwayScore = $(Match).find("teamAway score").attr('total');
            teamAwayScore = (typeof(teamAwayScore) == 'undefined') ? 'x' : teamAwayScore;

            var teamHome = $(Match).find("teamHome").attr('teamId');

            out+= "<td>"+teamHomeScore+' - '+teamAwayScore+"</td><td></td>";

        	that.saveResults(teamHomeScore, teamAwayScore, eventId );

		});


        out += '<td></td><td></td></tr>';

        //$(out).insertAfter('#round'+this.round+' .results');
        $('#round'+this.round).append(out);

	},

	loadTips : function() {
		// todo: load tips from db;
		var out = '';

		for(var user in this.tips ) {
			out += '<tr data-user="'+user+'"><td class="name">'+this.tips[user].name+'</td>';

			for (var tips in this.tips[user].tips) {
				var rot = (this.tips[user].rot == tips) ? rot = ' data-rot="true" class="rot"' : '';

				out += '<td data-spiel="'+tips+'" '+rot+' class="tiptd"><input type="tel" value="'+this.tips[user].tips[tips].home+'"  /> - <input type="tel" value="'+this.tips[user].tips[tips].away+'" /></td><td class="matchpoints" data-spiel="'+tips+'"></td>';
			}

			out += '<td><input type="checkbox" /></td><td class="punkte"></td></tr>';
		}

		$(out).insertAfter('#round'+this.round+' .tableHeader');

		this.calculatePoints();

	},

	calculatePoints : function() {
		var that = this;



		for(var i in tips[that.round]) {
			tips[this.round][i].punkte = 0;
		}

		$('#round'+this.round+' .matchpoints').each(function () {

			var spiel = $(this).data('spiel');

			var spieler = $(this).parent().data('user');

			var eventId = $(this).prev().data('eventid');

			var rot = $(this).prev().hasClass('rot') ? true : false;

			var tipp1 = $($(this).prev().find('input')[0]).val();

			var tipp2 = $($(this).prev().find('input')[1]).val();

			if(!$.isNumeric(tipp1) || !$.isNumeric(tipp2)) {
				return;
			}

			var eventId = $(spieltag.Matches).find('fixture[eventId='+eventId+']').attr('eventId');

			var erg1 = $(spieltag.Matches).find('fixture[eventId='+eventId+'] teamHome score').attr('total');

			var erg2 = $(spieltag.Matches).find('fixture[eventId='+eventId+'] teamAway score').attr('total');

			punkte = 0;

		  	tipp_sieger = (tipp1 == tipp2) ? 'unent' : (tipp1 > tipp2) ? 'heim' : 'gast';

		  	real_sieger = (erg1 == erg2) ? 'unent' : (erg1 > erg2) ? 'heim' : 'gast';

		  	if(real_sieger == tipp_sieger) {
		  	  punkte = 1;

		  	  if( (erg1 == tipp1) && (erg2 == tipp2) ) {
		  	    punkte = 2;
		  	  }

		  	  /* multiplikator */
		 		if(rot) punkte *= 2;
		  	}

		  	if(typeof(erg1) == 'undefined') {
		  		punkte = 0;
		  	}

		  	/* add points to tips object */
		  	//tips[that.round][spieler].punkte += punkte;

		  	that.savePoints(eventId, spieler, punkte);

		  	$(this).html(punkte);


		});

		sumPoints();
	},

	refresh: function(saveResults) {
		this.loadMatches(false);
	},

	saveResults: function(erg1, erg2, eventId) {
		if(erg1 != 'x') {
			$.ajax({type: "GET",
				url: "php/ajax.php",
				cache: false,
				data: { action: 'saveResult', erg1:erg1, erg2:erg2, eventId:eventId, rnd: getRandom()},
				dataType: 'json'});
		}
	},

	savePoints: function(eventId, user, punkte) {

		$.ajax({type: "GET",
				url: "php/ajax.php",
				cache: false,
				data: { action: 'savePoints', eventId:eventId, user:user, punkte:punkte, rnd: getRandom()},
				dataType: 'json'});
	}
};

/*	init Script */
	var actRound = actSpieltag;
	var spieltag = new Spieltag(actRound);

var timeoutID = window.setInterval(function(){ spieltag.refresh(true) }, 5000);


function getTable(target) {
	var zIndex = 1000;
    $.ajax({type: "GET", url: proxy+"?type=table&matchday="+(actRound-1), cache: false, dataType: "xml"})
    .done(function(xml) {

		$xml = $( xml );
	  	$title = $xml.find( "sports-title" );

	  	var out = '';

	  	$xml.find( "standing team").each(function () {
			var name = $(this).find('name').attr('full');
			var teamKey = $(this).find('team-metadata').attr('team-key');
	  		var teamPoints = $($(this).find('outcome-totals')[0]).attr('standing-points');
	  		out += '<div class="vTeam" style="z-index:' + zIndex + '"><div class="vLogo"><img src="svg/' + teamKey + '_original.svg" /></div>'
			out += '<div class="vPunkte">' + teamPoints + '</div>';
	  		out+='</div>';

	  		zIndex--;
	  	});

	  	$('#'+target).prepend(out);

    });
}

function getInjured(target) {
	$.ajax({type: "GET", url: proxy+"?type=injured", cache: false, dataType: "json"})
    .done(function(json) {
		var out = '';
    	for(var i in json) {
    		out+="<tr>";
    		out+="<td><img src='img/"+ json[i].typ +".png' title='"+ json[i].typ +"' alt='"+ json[i].typ +"'/></td>";
    		out+="<td>"+ json[i].name +"</td>";
    		out+="<td><span title='"+json[i].news+"'>";
    		out+= (json[i].news.length > 23) ? json[i].news.substring(0,23)+"..." : json[i].news;
    		out+= "</span></td>";
    		out+="<td class='tage'>"+ json[i].fehlt +"</td>";
    		out+="</tr>";
    	}

    	$('#'+target).append(out);
	});
}

function markWinner(spieltag) {
	var winner = -1;
	var winnercount = 0;
	$('#div'+spieltag+' .punkte').each(function() {
		var punkte = parseInt($(this).text());
		if (winner < punkte) {
			winner = punkte;
		}
	});

	$('#div'+spieltag+' .punkte').each(function() {
		var punkte = parseInt($(this).text());
		if (punkte == winner && winner > 0) {
			$(this).parent().addClass('dayWinner');


			winnercount++;
		}
	});

	$('#div'+spieltag+' .dayWinner').each(function() {
		var spieler = $(this).data('user');

		if (typeof(userPunkte[spieler]['wins'])  == 'undefined') {
			userPunkte[spieler]['wins'] = 0;
		}
			userPunkte[spieler]['wins']+= Math.floor(winAmount / winnercount);
	});

}
