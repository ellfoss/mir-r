var viewMembers = 'clan';
var clanData, events, visitors, clans, roles, members = [], memberID, medalsChangeSort = false;
var extendData = {
	member: null,
	game: null
};
var games = {
	wot: false,
	wotb: false,
	wowp: false,
	wows: false
};
var rights = {
	sadmin: false,
	admin: false,
	member: false,
	guest: true,
	str: 'guest'
};
var sheets = {
	'main': false,
	'extend': false,
	'admin': false,
	'graph': false,
	'stat': false,
	'event': false
};
var nameType = {
	name: 'Имя',
	image: 'Изображение',
	level: 'Уровень',
	type: 'Тип',
	nation: 'Нация',
	isPrem: 'Премиум',
	options: 'Опции',
	description: 'Описание',
	condition: 'Условие',
	section: 'Секция'
};
var medalTypes = {
	class: 'Классовые',
	custom: 'Специальные',
	repeatable: 'Повторяемые',
	series: 'Серийные',
	single: 'Одиночные',
	heroic: 'Героические'
};

var months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
var romanNum = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

String.prototype.toInt = function () {
	return this == '' ? 0 : parseInt(this);
};
String.prototype.toDate = function () {
	var date = new Date();
	var pattern = /(\d{4})-(\d{2})-(\d{2})/;
	if (pattern.test(this)) {
		var match = pattern.exec(this);
		date.setDate(match[3]);
		date.setYear(match[1]);
		date.setMonth(parseInt(match[2]) - 1);
		date.setDate(match[3]);
	}
	pattern = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;
	if (pattern.test(this)) {
		match = pattern.exec(this);
		date.setDate(match[3]);
		date.setYear(match[1]);
		date.setMonth(parseInt(match[2]) - 1);
		date.setDate(match[3]);
		date.setHours(match[4]);
		date.setMinutes(match[5]);
		date.setSeconds(match[6]);
	}
	pattern = /(\d{1,2}) ([а-я]{3}) (\d{2})/;
	if (pattern.test(this)) {
		match = pattern.exec(this);
		date.setDate(match[1]);
		date.setYear(parseInt('20' + match[3]));
		for (var m in months)
			if (match[2] == months[m].substr(0, 3))
				break;
		date.setMonth(parseInt(m));
		date.setDate(match[1]);
	}
	pattern = /(\d{1,2}) ([а-я]{3,8}) (\d{4})/;
	if (pattern.test(this)) {
		match = pattern.exec(this);
		date.setDate(match[1]);
		date.setYear(match[3]);
		for (m in months)
			if (match[2] == months[m])
				break;
		date.setMonth(m);
		date.setDate(match[1]);
	}
	pattern = /(\d{10})/;
	if (pattern.test(this))
		date = new Date(1000 * this);
	return date;
};
Date.prototype.toString = function (type) {
	var str = '';
	var m = (this.getMonth() + 1).toString();
	if (m.length == 1)
		m = '0' + m;
	if (type != undefined)
		m = months[this.getMonth()];
	if (type == 'short')
		m = m.substr(0, 3);
	var d = this.getDate().toString();
	if ((type == undefined) && d.length == 1)
		d = '0' + d;
	var y = this.getFullYear().toString();
	if (type == 'short')
		y = y.substr(2, 2);
	str = y + '-' + m + '-' + d;
	if (type != undefined)
		str = d + ' ' + m + ' ' + y;
	if (type == 'full')
		str = d + ' ' + m + ' ' + y;
	if (type == 'fulltime')
		str = d + ' ' + m + ' ' + y + ' ' + addZero(this.getHours()) + ':' + addZero(this.getMinutes()) + ':' + addZero(this.getSeconds());
	return str;
};
Date.prototype.toInt = function () {
	var newDate = new Date(this);
	newDate.setHours(0, 0, 0, 0);
	var number = newDate / (24 * 3600 * 1000);
	return number;
};
Date.prototype.wait = function () {
	var period = (new Date()).toInt() - this.toInt();
	return period;
};
Date.prototype.getDifference = function (day) {
	var diff = undefined;
	if (day instanceof Date) {
		diff = {
			'years': 0,
			'months': 0,
			'days': 0,
			'fullDays': 0
		};
		diff.fullDays = this.toInt() - day.toInt();
		diff.years = this.getFullYear() - day.getFullYear();
		diff.months = this.getMonth() - day.getMonth();
		diff.days = this.getDate() - day.getDate();
		if (diff.days < 0) {
			diff.days += 30;
			diff.months -= 1;
		}
		;
		if (diff.months < 0) {
			diff.months += 12;
			diff.years -= 1;
		}
		;
	}
	;
	return diff;
};
Number.prototype.oldToString = Number.prototype.toString;
Number.prototype.toString = function (radix, sign) {
	var ret = this.oldToString(radix).replace('.', ',');
	if (this > 0 && sign != undefined && sign == true)
		ret = '+' + ret;
	return ret;
};
Number.prototype.toStr = function (nums, sign) {
	if (nums == '')
		var ret = this.oldToString(10).replace('.', ',');
	else {
		var num = nums || 0;
		ret = (Math.round(Math.pow(10, num) * this) / Math.pow(10, num)).oldToString(10).replace('.', ',');
	}
	if (this > 0 && sign != undefined && sign == true)
		ret = '+' + ret;
	return ret;
};

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		module.exports = factory;
	} else {
		factory(jQuery);
	}
}
(function ($) {
	var toFix = ['wheel', 'mousewheel', 'DOMMouseScroll', 'MozMousePixelScroll'],
		toBind = ('onwheel' in document || document.documentMode >= 9) ?
			['wheel'] : ['mousewheel', 'DomMouseScroll', 'MozMousePixelScroll'],
		slice = Array.prototype.slice,
		nullLowestDeltaTimeout,
		lowestDelta;

	if ($.event.fixHooks) {
		for (var i = toFix.length; i;) {
			$.event.fixHooks[toFix[--i]] = $.event.mouseHooks;
		}
	}

	var special = $.event.special.mousewheel = {
		version: '3.1.12',

		setup: function () {
			if (this.addEventListener) {
				for (var i = toBind.length; i;) {
					this.addEventListener(toBind[--i], handler, false);
				}
			} else {
				this.onmousewheel = handler;
			}
			$.data(this, 'mousewheel-line-height', special.getLineHeight(this));
			$.data(this, 'mousewheel-page-height', special.getPageHeight(this));
		},

		teardown: function () {
			if (this.removeEventListener) {
				for (var i = toBind.length; i;) {
					this.removeEventListener(toBind[--i], handler, false);
				}
			} else {
				this.onmousewheel = null;
			}
			$.removeData(this, 'mousewheel-line-height');
			$.removeData(this, 'mousewheel-page-height');
		},

		getLineHeight: function (elem) {
			var $elem = $(elem),
				$parent = $elem['offsetParent' in $.fn ? 'offsetParent' : 'parent']();
			if (!$parent.length) {
				$parent = $('body');
			}
			return parseInt($parent.css('fontSize'), 10) || parseInt($elem.css('fontSize'), 10) || 16;
		},

		getPageHeight: function (elem) {
			return $(elem).height();
		},

		settings: {
			adjustOldDeltas: true,
			normalizeOffset: true
		}
	};

	$.fn.extend({
		mousewheel: function (fn) {
			return fn ? this.bind('mousewheel', fn) : this.trigger('mousewheel');
		},

		unmousewheel: function (fn) {
			return this.unbind('mousewheel', fn);
		}
	});

	function handler(event) {
		var orgEvent = event || window.event,
			args = slice.call(arguments, 1),
			delta = 0,
			deltaX = 0,
			deltaY = 0,
			absDelta = 0,
			offsetX = 0,
			offsetY = 0;
		event = $.event.fix(orgEvent);
		event.type = 'mousewheel';

		if ('detail' in orgEvent) {
			deltaY = orgEvent.detail * -1;
		}
		if ('wheelDelta' in orgEvent) {
			deltaY = orgEvent.wheelDelta;
		}
		if ('wheelDeltaY' in orgEvent) {
			deltaY = orgEvent.wheelDeltaY;
		}
		if ('wheelDeltaX' in orgEvent) {
			deltaX = orgEvent.wheelDeltaX * -1;
		}

		if ('axis' in orgEvent && orgEvent.axis === orgEvent.HORIZONTAL_AXIS) {
			deltaX = deltaY * -1;
			deltaY = 0;
		}

		delta = deltaY === 0 ? deltaX : deltaY;

		if ('deltaY' in orgEvent) {
			deltaY = orgEvent.deltaY * -1;
			delta = deltaY;
		}
		if ('deltaX' in orgEvent) {
			deltaX = orgEvent.deltaX;
			if (deltaY === 0) {
				delta = deltaX * -1;
			}
		}

		if (deltaY === 0 && deltaX === 0) {
			return;
		}

		if (orgEvent.deltaMode === 1) {
			var lineHeight = $.data(this, 'mousewheel-line-height');
			delta *= lineHeight;
			deltaY *= lineHeight;
			deltaX *= lineHeight;
		} else if (orgEvent.deltaMode === 2) {
			var pageHeight = $.data(this, 'mousewheel-page-height');
			delta *= pageHeight;
			deltaY *= pageHeight;
			deltaX *= pageHeight;
		}

		absDelta = Math.max(Math.abs(deltaY), Math.abs(deltaX));

		if (!lowestDelta || absDelta < lowestDelta) {
			lowestDelta = absDelta;

			if (shouldAdjustOldDeltas(orgEvent, absDelta)) {
				lowestDelta /= 40;
			}
		}

		if (shouldAdjustOldDeltas(orgEvent, absDelta)) {
			delta /= 40;
			deltaX /= 40;
			deltaY /= 40;
		}

		delta = Math[delta >= 1 ? 'floor' : 'ceil'](delta / lowestDelta);
		deltaX = Math[deltaX >= 1 ? 'floor' : 'ceil'](deltaX / lowestDelta);
		deltaY = Math[deltaY >= 1 ? 'floor' : 'ceil'](deltaY / lowestDelta);

		if (special.settings.normalizeOffset && this.getBoundingClientRect) {
			var boundingRect = this.getBoundingClientRect();
			offsetX = event.clientX - boundingRect.left;
			offsetY = event.clientY - boundingRect.top;
		}

		event.deltaX = deltaX;
		event.deltaY = deltaY;
		event.deltaFactor = lowestDelta;
		event.offsetX = offsetX;
		event.offsetY = offsetY;
		event.deltaMode = 0;

		args.unshift(event, delta, deltaX, deltaY);

		if (nullLowestDeltaTimeout) {
			clearTimeout(nullLowestDeltaTimeout);
		}
		nullLowestDeltaTimeout = setTimeout(nullLowestDelta, 200);

		return ($.event.dispatch || $.event.handle).apply(this, args);
	}

	function nullLowestDelta() {
		lowestDelta = null;
	}

	function shouldAdjustOldDeltas(orgEvent, absDelta) {
		return special.settings.adjustOldDeltas && orgEvent.type === 'mousewheel' && absDelta % 120 === 0;
	}

}));

SVG = {
	_NS: 'http://www.w3.org/2000/svg',
	_regexp: {
		istext: /text|tspan|tref/i,
		translate: /translate\(([-\d\.]+),?\s*([-\d\.]*?)\)/i,
		rotate: /rotate\(([\d\.]+),?.*?\)/i,
		scale: /scale\(([\d\.]+),?.*?\)/i
	},

	/// create svg element by name with agruments
	/// element's method:
	/// create - create any svg element
	/// append - append child
	/// attr - set, get any attributes
	/// text - set a text
	create: function (name, attributes) {
		var element = document.createElementNS(SVG._NS, name);
		element.create = SVG.create;
		element.append = SVG._append;
		element.attr = SVG._attr;
		element.translate = SVG._translate;
		element.scale = SVG._scale;
		/// text elements have attribute rotate
		if (SVG._regexp.istext.test(name) == false) {
			element.rotate = SVG._rotate;
		}
		element.setTransform = function (value) {
			this.attr('transform', value);
		};
		element.text = SVG._text;
		if (typeof(attributes) == 'object') {
			element.attr(attributes);
		}
		return element;
	},
	_append: function (child, attributes) {
		if (typeof child == 'string') {
			var new_child = this.create(child, attributes);
			this.append(new_child);
			return new_child;
		} else {
			this.appendChild(child);
		}
	},
	_text: function (text) {
		text = '' + text + '';
		text = new DOMParser().parseFromString(text, 'application/xhtml+xml').childNodes[0].childNodes[0];
		this.append(text);
	},
	_attr: function (attribute, value) {
		if (typeof attribute == 'object') {
			for (key in attribute) {
				this.setAttribute(key, attribute[key]);
			}
		} else if (typeof(attribute) == 'string') {
			if (typeof value != 'undefined') {
				this.setAttribute(attribute, value);
			} else {
				return this.getAttribute(attribute);
			}
		}
		;
	},

	_translate: function (x, y) {
		var operation = SVG._regexp.translate;
		var transform = null;
		var cx = 0;
		var cy = 0;
		var current_transform = this.attr('transform');
		if (current_transform != null) {
			var values = operation.exec(current_transform);
			if (values != null) {
				cx = (values[1] != '') ? parseFloat(values[1]) : 0;
				cy = (values[2] != '') ? parseFloat(values[2]) : 0;
			}
		}
		if (typeof(x) == 'number') {
			if (typeof(y) != 'number') {
				y = 0;
			}
			transform = 'translate(' + x + ',' + y + ')';
			if (current_transform != null) {
				if (operation.test(current_transform)) {
					transform = current_transform.replace(operation, transform);
				} else {
					transform = current_transform + ' ' + transform;
				}
			}
			this.attr('transform', transform);
		}
		return {
			'x': cx,
			'y': cy
		}
	},

	_scale: function (scale) {
		var operation = SVG._regexp.scale;
		var transform = null;
		var cscale = 1;
		var current_transform = this.attr('transform');
		if (current_transform != null) {
			var values = operation.exec(current_transform);
			if (values != null) {
				cscale = (values[1] != '') ? parseFloat(values[1]) : 1;
			}
		}
		if (typeof(scale) == 'number') {
			transform = 'scale(' + scale + ')';
			if (current_transform != null) {
				if (operation.test(current_transform)) {
					transform = current_transform.replace(operation, transform);
				} else {
					transform = current_transform + ' ' + transform;
				}
			}
			this.attr('transform', transform);
		}
		return {
			'scale': cscale
		}
	},

	_rotate: function (angle) {
		var operation = SVG._regexp.rotate;
		var transform = null;
		var cangle = 0;
		var current_transform = this.attr('transform');
		if (current_transform != null) {
			var values = operation.exec(current_transform);
			if (values != null) {
				cangle = (values[1] != '') ? parseFloat(values[1]) : 0;
			}
		}
		if (typeof(angle) == 'number') {
			transform = 'rotate(' + angle + ')';
			if (current_transform != null) {
				if (operation.test(current_transform)) {
					transform = current_transform.replace(operation, transform);
				} else {
					transform = current_transform + ' ' + transform;
				}
			}
			this.attr('transform', transform);
		}
		return {
			'angle': cangle
		}
	}
};
var Member = function (member) {
	this.id = member.id;
	this.name = member.name || null;
	this.rights = member.rights || 'guest';
	this.realName = member.rName || null;
	this.regDate = member.regDate.toDate() || new Date();
	this.regInClan = member.regInClan == null ? null : member.regInClan.toDate() || null;
	this.clan = member.clan || null;
	this.role = member.role == null ? null : member.role;
	this.games = member.games || null;
	this.color = member.color;

	this.setStat = function (game, stat, initDate) {
		if (game in this.games) {
			if (typeof(this.games[game]) != 'object')
				this.games[game] = {
					init: undefined,
					load: undefined,
					end: this.games[game],
					stat: new Array()
				};
			if (initDate != undefined)
				this.games[game].init = initDate;
			var lastStat = undefined;
			for (date in stat) {
				if (!(date in this.games[game].stat)) {
					if (stat[date]['type'] == 'full') {
						this.games[game].stat[date] = new Stat(game, stat[date]);
						lastStat = new Stat(game, this.games[game].stat[date]);
						if (this.games[game].load == undefined || date < this.games[game].load.toString())
							this.games[game].load = new Date(date);
					}
					;
					if (stat[date]['type'] == 'part' && lastStat != undefined) {
						this.games[game].stat[date] = new Stat(game, lastStat);
						this.games[game].stat[date].addStat(stat[date]);
						lastStat = new Stat(game, this.games[game].stat[date]);
						if (this.games[game].load == undefined || date < this.games[game].load.toString())
							this.games[game].load = new Date(date);
					}
					;
				}
				;
			}
			;
		}
		;
	};

	this.getStat = function (game, date) {
		var date = date || new Date();
		var ret = undefined;
		if (game in this.games && this.games[game] != null) {
			if (this.games[game].load != undefined && this.games[game].load <= date) {
				if (date.toString() in this.games[game].stat)
					ret = this.games[game].stat[date.toString()];
				else {
					var curDate = '2012-01-01';
					for (statDate in this.games[game].stat) {
						if (statDate < date.toString() && statDate > curDate)
							curDate = statDate;
					}
					;
					ret = this.games[game].stat[curDate];
				}
				;
			}
			;
		}
		;
		return ret;
	};
};

var Stat = function (game, stat2, stat1) {
	var that = this;
	this.game = game;
	this.main = new Object();
	if (stat1 != undefined) {
		var curStat1 = stat1;
		if (stat1 instanceof Stat)
			curStat1 = stat1.main;
	}
	var curStat2 = stat2;
	if (stat2 instanceof Stat)
		curStat2 = stat2.main;
	for (var variable in curStat2) {
		if (variable != 'date' && variable != 'id' && variable != 'type' && variable != 'medals' && variable != 'technics') {
			this.main[variable] = toInt(curStat2[variable]);
			if (stat1 != undefined) {
				if (variable in curStat1)
					this.main[variable] = curStat2[variable] - curStat1[variable];
				else
					this.main[variable] = curStat2[variable];
			}
			;
		}
		;
	}
	if ('medals' in stat2) {
		if (typeof(stat2.medals) == 'object') {
			this.medals = new Object();
			for (var medal in stat2.medals) {
				this.medals[medal] = toInt(stat2.medals[medal]);
				if (stat1 != undefined) {
					if ('medals' in stat1 && medal in stat1.medals)
						this.medals[medal] = stat2.medals[medal] - stat1.medals[medal];
					else
						this.medals[medal] = stat2.medals[medal];
					if (this.medals[medal] == 0)
						delete this.medals[medal];
				}
				;
			}
			;
		}
		;
	}
	if ('technics' in stat2) {
		if (typeof(stat2.technics) == 'object') {
			this.technics = new Object();
			for (var technic in stat2.technics) {
				if (typeof(stat2.technics[technic]) == 'object') {
					this.technics[technic] = new Object();
					for (variable in stat2.technics[technic]) {
						this.technics[technic][variable] = toInt(stat2.technics[technic][variable]);
						if (stat1 != undefined) {
							if ('technics' in stat1 && technic in stat1.technics && variable in stat1.technics[technic]) {
								this.technics[technic][variable] = stat2.technics[technic][variable] - stat1.technics[technic][variable];
							} else
								this.technics[technic][variable] = stat2.technics[technic][variable];
						}
						;
					}
					;
					if (!('0' in this.technics[technic]) || this.technics[technic][0] == 0)
						delete this.technics[technic];
				}
				;
			}
			;
		}
		;
	}
	this.ext = new Object();

	this.calculate = function () {
		that.ext.wins = that.main.battles == 0 ? 0 : Math.round(10000 * that.main.wins / that.main.battles) / 100;
		that.ext.losses = that.main.battles == 0 ? 0 : Math.round(10000 * that.main.losses / that.main.battles) / 100;
		that.ext.survived = that.main.battles == 0 ? 0 : Math.round(10000 * that.main.survived / that.main.battles) / 100;
		that.ext.xp = that.main.battles == 0 ? 0 : Math.round(10 * that.main.xp / that.main.battles) / 10;
		that.ext.frags = that.main.battles == 0 ? 0 : Math.round(100 * that.main.frags / that.main.battles) / 100;
		if (that.game == 'wot' || that.game == 'wotb') {
			that.ext.damage = that.main.battles == 0 ? 0 : Math.round(10 * that.main.damageD / that.main.battles) / 10;
			that.ext.spotted = that.main.battles == 0 ? 0 : Math.round(100 * that.main.spotted / that.main.battles) / 100;
		}
		if (that.game == 'wowp' || that.game == 'wows') {
			that.ext.damage = that.main.battles == 0 ? 0 : Math.round(10 * that.main.damage / that.main.battles) / 10;
		}
		if (that.game == 'wot' || that.game == 'wotb' || that.game == 'wowp') {
			that.ext.hits = that.main.shots == 0 ? 0 : Math.round(10000 * that.main.hits / that.main.shots) / 100;
		}
		if (that.game == 'wot' || that.game == 'wotb' || that.game == 'wows') {
			that.ext.capture = that.main.battles == 0 ? 0 : Math.round(100 * that.main.capture / that.main.battles) / 100;
			that.ext.dropped = that.main.battles == 0 ? 0 : Math.round(100 * that.main.dropped / that.main.battles) / 100;
		}
		if (that.game == 'wot') {
			Math.round(10 * (that.ext.effBS = that.main.battles == 0 ? 0 : Math.round(10 * (Math.log(that.main.battles) / 10 * (Math.round(that.ext.xp) + (that.main.damageD / that.main.battles) * ((that.main.wins / that.main.battles) * 2 + (that.main.frags / that.main.battles) * 0.9 + (that.main.spotted / that.main.battles) * 0.5 + (that.main.capture / that.main.battles) * 0.5 + (that.main.dropped / that.main.battles) * 0.5)))) / 10)) / 10;
		}

		if (that.main.battles != 0 && that.technics != undefined && Object.keys(that.technics).length) {
			var levels = {
				'1': 0,
				'2': 0,
				'3': 0,
				'4': 0,
				'5': 0,
				'6': 0,
				'7': 0,
				'8': 0,
				'9': 0,
				'10': 0
			};
			var exp = {
				dmg: 0,
				spot: 0,
				frag: 0,
				def: 0,
				win: 0
			};
			for (technic in that.technics) {
				if (technic in games[that.game].technics) {
					levels[games[that.game].technics[technic]['level']] += that.technics[technic][0];
					if (that.game == 'wot' && technic in games.wot.data.expectedTankValues) {
						exp.dmg += games.wot.data.expectedTankValues[technic].expDamage * that.technics[technic][0];
						exp.spot += games.wot.data.expectedTankValues[technic].expSpot * that.technics[technic][0];
						exp.frag += games.wot.data.expectedTankValues[technic].expFrag * that.technics[technic][0];
						exp.def += games.wot.data.expectedTankValues[technic].expDef * that.technics[technic][0];
						exp.win += games.wot.data.expectedTankValues[technic].expWinRate * that.technics[technic][0];
					}
					;
				}
				;
			}
			;

			that.ext.technics = 0;
			for (i = 1; i < 11; i++)
				that.ext.technics += i * levels[i] / that.main.battles;
			that.ext.technics = Math.round(100 * that.ext.technics) / 100;

			if (that.game == 'wot') {
				that.ext.effWN = Math.round(10 * (((that.main.damageD / that.main.battles) * (10 / (that.ext.technics + 2)) * (0.23 + 2 * that.ext.technics / 100)) + (that.main.frags / that.main.battles) * 250 + (that.main.spotted / that.main.battles) * 150 + (Math.log((that.main.capture / that.main.battles) + 1) / Math.log(1.732)) * 150 + (that.main.dropped / that.main.battles) * 150)) / 10;
				var minTier = Math.min(6, that.ext.technics);
				var minDef = Math.min(2.2, (that.main.dropped / that.main.battles));
				that.ext.effWN6 = Math.round(10 * ((1240 - 1040 / Math.pow(minTier, 0.164)) * (that.main.frags / that.main.battles) + (that.main.damageD / that.main.battles) * 530 / (184 * Math.exp(0.24 * that.ext.technics) + 130) + (that.main.spotted / that.main.battles) * 125 + minDef * 100 + ((185 / (0.17 + Math.exp(((100 * that.main.wins / that.main.battles) - 35) * (-0.134)))) - 500) * 0.45 + (6 - minTier) * (-60))) / 10;

				if (exp.dmg != 0) {
					exp.dmg = Math.max(0, ((that.main.damageD / exp.dmg) - 0.22) / (1 - 0.22));
					exp.spot = Math.max(0, Math.min(exp.dmg + 0.1, ((that.main.spotted / exp.spot) - 0.38) / (1 - 0.38)));
					exp.frag = Math.max(0, Math.min(exp.dmg + 0.2, ((that.main.frags / exp.frag) - 0.12) / (1 - 0.12)));
					exp.def = Math.max(0, Math.min(exp.dmg + 0.1, ((that.main.dropped / exp.def) - 0.10) / (1 - 0.10)));
					exp.win = Math.max(0, ((that.main.wins * 100 / exp.win) - 0.71) / (1 - 0.71));

					that.ext.effWN8 = Math.round(10 * (980 * exp.dmg + 210 * exp.dmg * exp.frag + 155 * exp.frag * exp.spot + 75 * exp.def * exp.frag + 145 * Math.min(1.8, exp.win))) / 10;
				}
				;
			}
			;
		}
	}

	this.addStat = function (stat) {
		var curStat = stat;
		if (stat instanceof Stat)
			curStat = stat.main;
		for (var variable in curStat) {
			if (variable != 'date' && variable != 'id' && variable != 'type' && variable != 'medals' && variable != 'technics') {
				if (variable in that.main)
					that.main[variable] += toInt(curStat[variable]);
				else
					that.main[variable] = toInt(curStat[variable]);
			}
			;
		}
		;
		if ('medals' in stat) {
			if (typeof(stat.medals) == 'object') {
				if (!('medals' in that))
					that.medals = new Object();
				for (var medal in stat.medals) {
					if (medal in that.medals)
						that.medals[medal] += toInt(stat.medals[medal]);
					else
						that.medals[medal] = toInt(stat.medals[medal]);
				}
				;
			}
			;
		}
		;
		if ('technics' in stat) {
			if (typeof(stat.technics) == 'object') {
				if (!('technics' in that))
					that.technics = new Object();
				for (var technic in stat.technics) {
					if (typeof(stat.technics[technic]) == 'object') {
						if (!(technic in that.technics))
							that.technics[technic] = new Object();
						for (variable in stat.technics[technic]) {
							if (!(variable in that.technics[technic]))
								that.technics[technic][variable] = 0;
							that.technics[technic][variable] += toInt(stat.technics[technic][variable]);
						}
						;
					}
					;
				}
				;
			}
			;
		}
		;
		that.calculate();
	};

	this.calculate();
};

$.fn.table = function (options, changeSort) {
	var that = this;
	var options = $.extend({
		header: [], // 0 - наименование заголовка
		// 1 - значение в ячейке
		// 2 - параметр сортировки
		// 3 - ширина ячейки, по-умолчанию 50
		// 4 - начальное направление сортировки
		// 5 - выравнивание текста, по-умолчанию center
		data: [],
		curSort: '',
		curnap: 0,
		headHeight: 50,
		lineHeight: 50,
		width: 950
	}, options);

	this.sort = function (stb, href) {
		var sort = options.header[stb][2];
		if (typeof(sort) == 'object')
			sort = sort[href.substr(1, 1)];

		var newTable = new Array();
		for (var row in options.data) {
			var len = Object.keys(newTable).length;
			if (len == 0 || sort == options.curSort)
				newTable.splice(0, 0, options.data[row]);
			else {
				for (var i = len; i--; i >= 0) {
					var res1 = options.data[row][sort];
					var res2 = newTable[i][sort];
					if (typeof(res1) == 'string') {
						res1 = res1.toLowerCase();
						res2 = res2.toLowerCase();
					}
					;
					var res = (res1 > res2 ? 1 : res1 < res2 ? -1 : 0) * options.header[stb][4];
					if (res >= 0)
						break;
				}
				;
				newTable.splice(i + 1, 0, options.data[row]);
			}
			;
		}
		;
		options.data = new Array();
		options.data = copyArray(newTable);
		options.curSort = sort;
		that.state();
	}

	this.state = function () {
		for (row in options.data) {
			var number = options.data[row]['number'];
			var element = $(this).find('.row[row="' + number + '"]');
			var top = (row - number) * options.lineHeight;
			$(this).find('.row[row="' + number + '"]').animate({
					top: top
				}, 500,
				function () {
					$(this).css({
						'z-index': 500
					})
				});
		}
		;
		if (changeSort != undefined)
			changeSort(options.data);
	}

	this.checkSize = function () {
		var rowsH = rows.height();
		var inTableH = inTable.height();
		if (inTableH != 0) {
			if (rowsH > inTableH) {
				scrollbar.css({
					display: 'inline-block'
				});
				var maxTop = inTableH - rowsH;
				var top = parseInt(rows.css('top'));
				if (top < maxTop)
					top = maxTop;
				if (top > 0)
					top = 0;
				rows.css({
					top: top
				});
				var spanH = Math.round(scrollbar.height() * inTableH / (rowsH + 6));
				var spanT = -1 * Math.ceil(scrollbar.height() * top / rowsH);
				scrollbar.find('span').css({
					height: spanH,
					top: spanT
				});
			} else {
				scrollbar.css({
					display: 'none'
				});
				rows.css({
					top: 0
				});
			}
			;
		}
		;
	}

	this.checkScroll = function () {
		var rowsH = rows.height();
		var inTableH = inTable.height();
		var maxTop = inTableH - rowsH;
		var spanT = parseInt(scrollbar.find('span').css('top'));
		var top = -1 * Math.ceil(rowsH * spanT / (scrollbar.height() - 6));
		if (top < maxTop)
			top = maxTop;
		rows.css({
			top: (top.toString() + 'px')
		});
		that.checkSize();
	}

	var minWidth = 0;
	for (j in options.header) {
		if (!(2 in options.header[j]))
			options.header[j][2] = options.header[j][1];
		if (!(4 in options.header[j]))
			options.header[j][4] = 1;
		if (!(3 in options.header[j]))
			minWidth += 50;
		else
			minWidth += options.header[j][3];
	}
	;

	$(this).html('').addClass('table');
	if (options.data.length) {
		var table = $('<div class="out-table"></div>').appendTo(this);
		this.css({
			'min-width': minWidth,
			'max-height': (options.data.length) * options.lineHeight + options.headHeight,
			'min-height': 2 * options.lineHeight
		})
		var scrollbar = $('<div class="scrollbar"><span></span></div>').appendTo(this);
		var header = $('<div class="header"></div>').appendTo(table);
		var inTable = $('<div class="in-table"></div>').appendTo(table);
		inTable.css({
			'max-height': 'calc(100% - ' + options.headHeight + 'px)'
		})
		var rows = $('<div class="rows"></div>').appendTo(inTable);
		rows.css({
			height: options.data.length * options.lineHeight
		})
		for (var col in options.header) {
			var cell = $('<div class="cell"></div>').appendTo(header);
			cell.attr('col', col);
			var inCell = $('<span></span>').appendTo(cell);
			inCell.html(options.header[col][0]);
			if (options.header[col][4] != 0) {
				if (typeof(options.header[col][0]) != 'object')
					inCell.html(options.header[col][2] == '' ? options.header[col][0] : ('<a sort="' + col + '">' + options.header[col][0] + '</a>'));
				else {
					var innerText = '';
					if (typeof(options.header[col][2]) != 'object')
						innerText = '<a sort="' + col + '">' + options.header[col][0][0] + '</a><br />' + options.header[col][0][1];
					else
						for (i in options.header[col][0])
							innerText += '<a sort="' + col + '" href="#' + i + '">' + options.header[col][0][i] + '</a><br />';
					inCell.html(innerText);
				}
				;
			}
			;
		}
		;

		var number = 0;
		for (var row in options.data) {
			var colorClass = '';
			if ('bg-class' in options.data[row])
				colorClass = options.data[row]['bg-class'];
			var tableLine = $('<div class="row' + colorClass + '" row="' + row + '"></div>').appendTo(rows);
			rows.append('<br />');
			if ('lineClass' in options.data[row])
				tableLine.addClass(options.data[row]['lineClass']);
			for (col in options.header) {
				cell = $('<div class="cell"></div>').appendTo(tableLine);
				cell.attr('col', col);
				var str = options.data[row][options.header[col][1]];
				if (typeof(str) == 'string') {
					var posColor = str.indexOf('<c:');
					if (posColor >= 0) {
						var color = str.substring(posColor);
						var varColor = color.substring(3, color.indexOf(','));
						var waySort = varColor.substr(varColor.length - 1);
						var numColor = color.substring(color.indexOf(',') + 1, color.indexOf('>'));
						var tagColor = '<c:' + varColor + ',' + numColor + '>';
						if (waySort == '-')
							varColor = varColor.substring(0, varColor.length - 1);
						var min = findCrit(options.data, varColor, 'min');
						var max = findCrit(options.data, varColor, 'max');
						var curColor = colorDiff(min, numColor, max, options.data[row][varColor]);
						if (waySort == '-')
							curColor = colorDiff(max, numColor, min, options.data[row][varColor]);
						str = replace_string(str, tagColor, '<span style="color:' + curColor + ';">');
						str = replace_string(str, '</c>', '</span>');
					}
					;
					var bgColor = '';
					posColor = str.indexOf('<cb:');
					if (posColor >= 0) {
						color = str.substring(posColor);
						bgColor = color.substring(4, color.indexOf('>'));
						str = str.substring(str.indexOf('>') + 1);
					}
					;
				}
				;
				if (str == '' || typeof(str) == 'undefined')
					str = '&nbsp;';
				cell.html(str);
				if (bgColor != '')
					cell.css({
						'background-color': bgColor
					});
			}
			;
			options.data[row]['number'] = row;
			number++;
		}
		;

		for (var col in options.header) {
			$(this).find('.cell[col="' + col + '"]').css({
				'width': options.header[col][3] + 'px',
				'max-width': options.header[col][3] + 'px',
				'height': options.lineHeight
			});
			if ('5' in options.header[col]) {
				$(this).find('.cell[col="' + col + '"]').addClass(options.header[col][5]);
			}
		}
		;
		header.find('.cell').each(function () {
			$(this).css({
				height: options.headHeight
			});
		});

		this.checkSize();
		$(window).resize(function () {
			if (!($(that).is(':hidden')))
				that.checkSize();
		});

		rows.mousewheel(function (event, delta) {
			var top = parseInt(rows.css('top'));
			top += options.lineHeight * delta;
			rows.css({
				top: top
			});
			that.checkSize();
		});

		scrollbar.find('span').draggable({
			containment: 'parent',
			axis: 'y',
			drag: function () {
				that.checkScroll();
			}
		});

		$(this).find('.header .cell a').click(function () {
			that.sort($(this).attr('sort'), $(this).attr('href'));
			that.lastStb = $(this).attr('sort');
			return false;
		});
		var topClearPos = 0;
		$(that).find('.row').draggable({
			axis: 'y',
			containment: 'parent',
			start: function () {
				$(this).css({
					'z-index': 1000
				});
				topClearPos = $(this).css('top').toInt() + $(this).attr('row') * options.lineHeight;
			},
			drag: function () {
				var curMovePos = $(this).css('top').toInt() + $(this).attr('row') * options.lineHeight;
				var moved = new Array();
				var curPos = -1;
				for (var row in options.data) {
					var top = row * options.lineHeight;
					if (top != topClearPos) {
						if (curMovePos > topClearPos && top > topClearPos && curMovePos > (top - options.lineHeight / 2)) {
							moved.push(row);
						}
						;
						if (curMovePos < topClearPos && top < topClearPos && curMovePos < (top + options.lineHeight / 2)) {
							moved.push(row);
						}
						;
					} else
						curPos = row.toInt();
				}
				;
				var len = Object.keys(moved).length;
				if (len > 0) {
					var sign = 0;
					if (curMovePos > topClearPos) {
						for (move in moved) {
							var number = options.data[moved[move]]['number'].toInt();
							row = '.row[row="' + number + '"]';
							top = (moved[move].toInt() - number - 1) * options.lineHeight;
							$(this).parent().find(row).stop().animate({
								'top': top
							});
						}
						;
						options.data.splice(curPos + len + 1, 0, options.data[curPos]);
						options.data.splice(curPos, 1);
						sign = 1;
					}
					;
					if (curMovePos < topClearPos) {
						for (move in moved) {
							var number = options.data[moved[move]]['number'].toInt();
							row = '.row[row="' + number + '"]';
							top = (moved[move].toInt() - number + 1) * options.lineHeight;
							$(this).parent().find(row).stop().animate({
								'top': top
							});
						}
						;
						options.data.splice(curPos - len, 0, options.data[curPos]);
						options.data.splice(curPos + 1, 1);
						sign = -1;
					}
					;
					topClearPos = (curPos + len * sign) * options.lineHeight;
					options.curSort = '';
				}
				;
			},
			stop: function () {
				that.state();
			}
		});
	}
	;

	return this;
};

$(document).ready(function (e) {
	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		prevText: '&#x3c;Пред',
		nextText: 'След&#x3e;',
		currentText: 'Сегодня',
		monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
			'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		monthNamesShort: ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
			'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
		dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
		dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
		dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
		weekHeader: 'Не',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: '',
		showOn: 'button',
		buttonImage: 'images/bCalendar.png',
		buttonImageOnly: true,
		dateFormat: 'd M yy',
		showAnim: 'blind'
	};
	$.datepicker.setDefaults($.datepicker.regional['ru']);
	$(window).resize(function () {
		resize();
	});

	$('.changeDate .rewind').button({
		text: false,
		icons: {
			primary: 'ui-icon-seek-prev'
		}
	});
	$('.changeDate .forward').button({
		text: false,
		icons: {
			primary: 'ui-icon-seek-next'
		}
	});
	$('.changeDate .inputDate').datepicker();
	$('.changeDate .rewind, .changeDate .forward').click(function () {
		if ($(this).parent().attr('type') == 'day') {
			var date = checkDate($(this).parent());
			date.setDate(date.getDate() + parseInt($(this).attr('step')));
			$(this).parent().find('.inputDate').val(date.toString('long'));
			checkDate($(this).parent());
		}
		;
		if ($(this).parent().attr('type') == 'period') {
			date = checkDate($(this).parent());
			var period = $(this).attr('period');
			var step = parseInt($(this).attr('step'));
			var oldValue1 = new Date(date[0]);
			var oldValue2 = new Date(date[1]);
			var newValue1 = new Date(date[0]);
			var newValue2 = new Date(date[1]);
			if (period == 'day') {
				newValue1.setDate(newValue1.getDate() + 1 * step);
				newValue2.setDate(newValue2.getDate() + 1 * step);
			}
			;
			if (period == 'week') {
				newValue1.setDate(newValue1.getDate() + 7 * step);
				newValue2.setDate(newValue2.getDate() + 7 * step);
			}
			;
			if (period == 'month') {
				newValue1.setMonth(newValue1.getMonth() + 1 * step);
				newValue2.setMonth(newValue2.getMonth() + 1 * step);
			}
			;
			if (period == 'year') {
				newValue1.setMonth(newValue1.getMonth() + 12 * step);
				newValue2.setMonth(newValue2.getMonth() + 12 * step);
			}
			;
			$(this).parent().find('.inputDate[name="date1"]').val(newValue1.toString('long'));
			$(this).parent().find('.inputDate[name="date2"]').val(newValue2.toString('long'));
			date = checkDate($(this).parent());
			if (date[0].toString() != newValue1.toString() || date[1].toString() != newValue2.toString()) {
				$(this).parent().find('.inputDate[name="date1"]').val(oldValue1.toString('long'));
				$(this).parent().find('.inputDate[name="date2"]').val(oldValue2.toString('long'));
				checkDate($(this).parent());
			}
			;
		}
		;

		var func = $(this).parent().attr('action');
		eval(func);
	});
	$('.changeDate input').change(function () {
		checkDate($(this).parent());
		var func = $(this).parent().attr('action');
		eval(func);
	});

	$(document).click(function (event) {
		if (!($(event.target).closest('.newName').length)) {
			$('.newName').each(function () {
				$(this).remove();
			});
		}
		;
		if (!($(event.target).closest('#formAddMember .form').length) && !($(event.target).closest('[action="addMember"] .button').length))
			closeAddMember();
	});
	$('button').live('click', function () {
		$(this).blur();
	});

	$('#message').click(function () {
		showMessage('');
	});

	checkMember();

	$('.button').each(function () {
		$(this).html('<div class="hover"></div><div class="text">' + $(this).html() + '</div>');
	});
	$("#initPeriodData, #finPeriodData").datepicker({
		showOn: 'button',
		buttonImage: 'images/bCalendar.png',
		buttonImageOnly: true,
		dateFormat: 'd M y',
		showAnim: 'blind'
	});
	$("#rewDay").button({
		text: false,
		icons: {
			primary: "ui-icon-seek-prev"
		}
	});
	$("#forDay").button({
		text: false,
		icons: {
			primary: "ui-icon-seek-next"
		}
	});

	$('.button').click(function () {
		var action = $(this).parent().attr('action');
		var type = $(this).parent().attr('type');
		if (type == 'buttonRadio') {
			if (!$(this).hasClass('active')) {
				$(this).parent().find('.button').removeClass('active');
				$(this).addClass('active');
				eval(action + '()');
			}
			;
		}
		;
		if (type == 'buttonCheck') {
			if ($(this).hasClass('active'))
				$(this).removeClass('active');
			else
				$(this).addClass('active');
			eval(action + '()');
		}
		;
		if (type == 'button')
			eval(action + '()');
	});
	$('.borderBlock .ui-icon').click(function () {
		var block = $(this).parent().parent();
		block.toggleClass('hide');
	});
	$('#menuLogin').click(function () {
		if (memberID == '')
			document.location.href = 'openID.php?action=verify';
		else
			document.location.href = 'openID.php?action=exit';
	});
	$('#adminTechnicsSelectors .confirm[confirm="new"]').click(function () {
		var list = new Array();
		var game = $('#adminTechnicsMenu .button.active').attr('menu');
		var table = $('#adminTableTechnics .table .row');
		for (var row = 0; row < table.length; row++) {
			var technicID = $(table[row]).find('.technic').attr('technicID');
			if (games[game].technics[technicID].state == 'new')
				list.push(technicID);
		}
		;
		if (list.length) {
			wait($('#adminTechnics'));
			myQuery('ajax/setChanges.php', {
				game: game,
				state: 'new',
				type: 'technic',
				list: list
			}, function (answer) {
				wait($('#adminTechnics'), false);
				for (var game in answer.data) {
					for (var technic in answer.data[game].technics)
						games[game].technics[technic] = answer.data[game].technics[technic];
					adminTechniksList();
				}
				;
			});
		}
		;
	});
	$('#adminMedalsSelectors .confirm[confirm="new"]').click(function () {
		var list = new Array();
		var game = $('#adminMedalsMenu .button.active').attr('menu');
		var table = $('#adminTableMedals .table .row');
		for (var row = 0; row < table.length; row++) {
			var medal = $(table[row]).find('.medal').attr('medal');
			if (games[game].medals[medal].state == 'new')
				list.push(medal);
		}
		;
		if (list.length) {
			wait($('#adminMedals'));
			myQuery('ajax/setChanges.php', {
				game: game,
				state: 'new',
				type: 'medal',
				list: list
			}, function (answer) {
				wait($('#adminMedals'), false);
				for (var game in answer.data) {
					for (var medal in answer.data[game].medals)
						games[game].medals[medal] = answer.data[game].medals[medal];
					adminMedalsList();
				}
				;
			});
		}
		;
	});
	$('#adminTechnicsSelectors .confirm[confirm="chn"]').click(function () {
		var list = new Array();
		var typesChange = new Array();
		var game = $('#adminTechnicsMenu .button.active').attr('menu');
		var table = $('#adminTableTechnics .table .row');
		for (var row = 0; row < table.length; row++) {
			var technicID = $(table[row]).find('.technic').attr('technicID');
			if (games[game].technics[technicID].state == 'chn')
				list.push(technicID);
			for (type in games[game].technicChanges[technicID]) {
				if (type != 'id' && games[game].technicChanges[technicID][type] != null) {
					if (!(type in typesChange))
						typesChange[type] = 1;
					else
						typesChange[type]++;
				}
				;
			}
			;
		}
		;
		var countTypesChanges = Object.keys(typesChange).length;
		if (countTypesChanges) {
			wait($('#adminTechnics'));
			myQuery('ajax/setChanges.php', {
				game: game,
				state: 'chn',
				type: 'technic',
				list: list,
				change: 'all'
			}, function (answer) {
				wait($('#adminTechnics'), false);
				for (var game in answer.data) {
					if ('technics' in answer.data[game])
						for (var technic in answer.data[game].technics)
							games[game].technics[technic] = answer.data[game].technics[technic];
					if ('technicChanges' in answer.data[game])
						for (technic in answer.data[game].technicChanges) {
							if (answer.data[game].technicChanges[technic] == null)
								delete games[game].technicChanges[technic];
							else
								games[game].technicChanges[technic] = answer.data[game].technicChanges[technic];
						}
					;
				}
				;
				adminTechniksList();
			});
		}
		;
	});
	$('#adminMedalsSelectors .confirm[confirm="chn"]').click(function () {
		var list = new Array();
		var typesChange = new Array();
		var game = $('#adminMedalsMenu .button.active').attr('menu');
		var table = $('#adminTableMedals .table .row');
		for (var row = 0; row < table.length; row++) {
			var medal = $(table[row]).find('.medal').attr('medal');
			if (games[game].medals[medal].state == 'chn')
				list.push(medal);
			for (type in games[game].medalChanges[medal]) {
				if (type != 'id' && games[game].medalChanges[medal][type] != null) {
					if (!(type in typesChange))
						typesChange[type] = 1;
					else
						typesChange[type]++;
				}
				;
			}
			;
		}
		;
		var countTypesChanges = Object.keys(typesChange).length;
		if (countTypesChanges) {
			wait($('#adminMedals'));
			myQuery('ajax/setChanges.php', {
				game: game,
				state: 'chn',
				type: 'medal',
				list: list,
				change: 'all'
			}, function (answer) {
				wait($('#adminMedals'), false);
				for (var game in answer.data) {
					if ('medals' in answer.data[game])
						for (var medal in answer.data[game].medals)
							games[game].medals[medal] = answer.data[game].medals[medal];
					if ('medalChanges' in answer.data[game])
						for (medal in answer.data[game].medalChanges) {
							if (answer.data[game].medalChanges[medal] == null)
								delete games[game].medalChanges[medal];
							else
								games[game].medalChanges[medal] = answer.data[game].medalChanges[medal];
						}
					;
				}
				;
				adminMedalsList();
			});
		}
		;
	});
	$('#formAddMember .block-find input').keyup(function (e) {
		if (e.which == 13)
			findMember();
	});

	clickMenu('main');
	resize();
});

function resize() {
	var windowH = $(window).height();
	var headerH = $('header').height();
	var mainH = windowH - headerH;
	var mainW = $('#main').width();
	$('.sheet').width(mainW);

	var messageBlock = $('#messageBlock');
	var messageH = messageBlock.height();
	messageBlock.css({
		top: 'calc(50% - ' + messageH + 'px)'
	});

	$('.h-container').each(function () {
		var height = $(this).height();
		var heightAuto = $(this).children('.h-auto').outerHeight();
		var heightFull = height - heightAuto;
		$(this).children('.h-full').height(heightFull);
	});
}

function checkMember() {
	var member = $('#member');
	memberID = member.attr('member');
	rights.str = member.attr('rights');
	if (rights.str == 'sadmin') {
		rights.sadmin = true;
		rights.admin = true;
		rights.member = true;
	}
	if (rights.str == 'admin') {
		rights.admin = true;
		rights.member = true;
	}
	if (rights.str == 'member')
		rights.member = true;
}

function wait(source, action) {
	if (action === undefined || action == true) {
		if ($(source).find('.wait').length == 0) {
			var wait = $('<div class="wait"></div>').appendTo(source);
			wait.html($('#wait').html());
		}
	}
	if (action == false)
		$(source).find('.wait').remove();
	resize();
}

function buttonMain() {
	var menu = $('#mainCommands').find('.button.active').attr('menu');
	clickMenu(menu);
}

function buttonAll() {
	if ($('#menuAll.button').hasClass('active'))
		viewMembers = 'all';
	else
		viewMembers = 'clan';
	var menu = $('#mainCommands .button.active').attr('menu');
	clickMenu(menu);
}

function clickMenu(menu) {
	var curSheet = $('.sheet.active').attr('order');
	var newSheet = $('.sheet[menu="' + menu + '"]').attr('order');
	if (curSheet < newSheet) {
		$('.sheet[order="' + newSheet + '"]').addClass('active');
		resize();
		$('#mainContainer').animate({
			left: (-1 * parseInt($('.sheet[order="' + curSheet + '"]').width()))
		}, 700, function () {
			$('.sheet[order="' + curSheet + '"]').removeClass('active');
			$('#mainContainer').css({
				left: 0
			});
			if (sheets[menu] != viewMembers)
				initSheet(menu);
		});
	} else if (curSheet > newSheet) {
		$('.sheet[order="' + newSheet + '"]').addClass('active');
		resize();
		$('#mainContainer').css({
			left: (-1 * parseInt($('.sheet[order="' + curSheet + '"]').width()))
		});
		$('#mainContainer').animate({
			left: 0
		}, 700, function () {
			$('.sheet[order="' + curSheet + '"]').removeClass('active');
			if (sheets[menu] != viewMembers)
				initSheet(menu);
		});
	} else if (sheets[menu] != viewMembers)
		initSheet(menu);
}

function initSheet(item) {
	if (item == 'main') {
		if (sheets.main == false)
			query('main');
		else
			mainSheet();
	}
	;
	if (item == 'extend') {
		extendSheet();
	}
	;
	if (item == 'admin') {
		if (sheets.admin == false)
			query('admin');
		else
			adminSheet();
	}
	;
	if (item == 'graph') {
		graphSheet();
	}
	;
	if (item == 'stat') {
		statSheet();
	}
	;
}

function mainSheet() {
	sheets.main = viewMembers;
	var table = new Array();
	var row = 0;
	for (var member in members) {
		var today = new Date();
		if (members[member].clan == clanData.id || viewMembers == 'all') {
			table[row] = new Array();
			table[row]['nick'] = members[member].name;
			var nameClass = 'name';
			if (memberID == member)
				nameClass += ' myself';
			table[row]['name'] = '<span class="' + nameClass + '" name="' + members[member].name + '" member="' + member + '">' + members[member].name + '</span>&nbsp;';
			if (members[member].clan == null) {
				table[row]['din'] = today.toString();
				table[row]['sdin'] = '';
				table[row]['per'] = '';
			} else {
				table[row]['din'] = members[member].regInClan.toString();
				table[row]['sdin'] = '<span class="inClan" member="' + member + '">' + members[member].regInClan.toString('long') + '</span>';
				table[row]['per'] = members[member].role;
				table[row]['sper'] = '<span>' + roles[members[member].role] + '</span>';
			}
			;
			table[row]['clan'] = members[member].clan == null ? 'zzzzz' : clans[members[member].clan].tag;
			table[row]['sclan'] = members[member].clan == null ? '' : '<span class="clan" clan="' + members[member].clan + '">' + clans[members[member].clan].tag + '</span>';
			table[row]['wot'] = '';
			table[row]['swot'] = '';
			table[row]['wotb'] = '';
			table[row]['swotb'] = '';
			table[row]['wowp'] = '';
			table[row]['swowp'] = '';
			table[row]['wows'] = '';
			table[row]['swows'] = '';
			table[row]['wotg'] = '';
			table[row]['swotg'] = '';
			for (var game in members[member].games) {
				if (members[member].games[game] != undefined) {
					if (members[member].games[game] != null) {
						var gameDate = undefined;
						if (typeof(members[member].games[game]) == 'string')
							gameDate = members[member].games[game];
						if (typeof(members[member].games[game]) == 'object') {
							if ('end' in members[member].games[game])
								gameDate = members[member].games[game].end;
						}
						;
						if (gameDate != undefined) {
							table[row][game] = gameDate;
							table[row]['s' + game] = '<span><image class="inGame" game="' + game + '" wait="' + gameDate.toDate().wait() + '" time="' + gameDate.slice(-8) + '" style="opacity:' + opacityOfDate(gameDate) + '" src="images/logo/logo_' + game + '.png" /></span>';
						}
						;
					} else {
						table[row][game] = ' ';
						table[row]['s' + game] = '<span><image class="inGame" wait="null" style="opacity:0.2" src="images/logo/logo_' + game + '.png" /></span>';
					}
				}
				;
			}
			;
			if ('wotg' in members[member].games) {
				table[row]['wotg'] = ' ';
				table[row]['swotg'] = '<span><image src="images/logo/logo_wotg.png" /></span>';
			}
			;
			row++;
		}
		;
	}
	;

	if (rights.admin)
		$('#countMembers>span').html('Количество: ' + table.length + ' чел.');
	table.sort(function (a, b) {
		if (a['din'] > b['din'])
			return 1;
		else if (a['din'] < b['din'])
			return -1;
		else
			return 0;
	});
	var all = true;
	if (viewMembers == 'clan')
		all = false;
	var mainHead = new Array();
	mainHead[0] = ['Имя', 'name', 'nick', 140, 1, 'left'];
	if (all)
		mainHead[1] = ['Клан', 'sclan', 'clan', 60, 1, 'left'];
	mainHead[all ? 2 : 1] = ['Должность', 'sper', 'per', 190, 1, 'left'];
	mainHead[all ? 3 : 2] = ['Дата вступления', 'sdin', 'din', 160, 1, 'left'];
	mainHead[all ? 4 : 3] = ['WoT', 'swot', 'wot', 60, -1, 'center'];
	mainHead[all ? 5 : 4] = ['WoTB', 'swotb', 'wotb', 60, -1, 'center'];
	mainHead[all ? 6 : 5] = ['WoWP', 'swowp', 'wowp', 80, -1, 'center'];
	mainHead[all ? 7 : 6] = ['WoWS', 'swows', 'wows', 60, -1, 'center'];
	mainHead[all ? 8 : 7] = ['WoTG', 'swotg', 'wotg', 60, -1, 'center'];
	$('#tabMain .table').table({
		header: mainHead,
		data: table,
		lineHeight: 50
	});

	$('#tabMain .inGame').each(function () {
		var s = $(this).attr('wait');
		if (s == 'null')
			s = 'не играл';
		else {
			s = s + ' ' + getWordEnding(s)[2];
			if (s == '0 дней')
				s = 'сегодня ' + $(this).attr('time');
			if (s == '1 день')
				s = 'вчера ' + $(this).attr('time');
		}
		;
		$(this).parent().attr('title', '').tooltip({
			content: s,
			position: {
				my: 'left top',
				at: 'right top'
			},
			hide: {
				duration: 0
			},
			tooltipClass: 'tooltip'
		});
	});
	$('#tabMain .inClan').each(function () {
		var member = members[$(this).attr('member')];
		var diff = today.getDifference(member.regInClan);
		var s = diff.fullDays + ' ' + getWordEnding(diff.fullDays)[2];
		if ((diff.years > 0 || diff.months > 0)) {
			s += '<br />(';
			s += diff.years > 0 ? (diff.years + ' ' + getWordEnding(diff.years)[0]) : '';
			s += diff.months > 0 ? ((diff.years > 0 ? ' ' : '') + diff.months + ' ' + getWordEnding(diff.months)[1]) : '';
			s += diff.days > 0 ? (' ' + diff.days + ' ' + getWordEnding(diff.days)[2] + ')') : ')';
		}
		;
		$(this).parent().attr('title', '').tooltip({
			content: s,
			position: {
				my: "left top",
				at: "right top"
			},
			hide: {
				duration: 0
			},
			tooltipClass: 'tooltip'
		});
	});
	$('#tabMain .clan').each(function () {
		var clanID = $(this).attr('clan');
		$(this).css({
			color: clans[clanID].color
		});
		var s = '<img src="' + clans[clanID].emblem + '" style="width:32px; height:32px" \> ' + clans[clanID].name;
		$(this).parent().attr('title', '').tooltip({
			content: s,
			position: {
				my: "left top",
				at: "right top"
			},
			hide: {
				duration: 0
			},
			tooltipClass: 'tooltip'
		});
	});
	$('#tabMain .name').each(function () {
		var member = members[$(this).attr('member')];
		var s = member.realName == null ? '' : member.realName;
		if (rights.admin) {
			var diff = today.getDifference(member.regDate);
			if (s != '')
				s += '<br />';
			s += member.regDate.toString('long') + '<br />' + diff.fullDays + ' ' + getWordEnding(diff.fullDays)[2];
			if ((diff.years > 0 || diff.months > 0)) {
				s += '<br />(';
				s += diff.years > 0 ? (diff.years + ' ' + getWordEnding(diff.years)[0]) : '';
				s += diff.months > 0 ? ((diff.years > 0 ? ' ' : '') + diff.months + ' ' + getWordEnding(diff.months)[1]) : '';
				s += diff.days > 0 ? (' ' + diff.days + ' ' + getWordEnding(diff.days)[2] + ')') : ')';
			}
			;
		}
		;
		$(this).parent().attr('title', '').tooltip({
			content: s,
			position: {
				my: "left top",
				at: "right top"
			},
			hide: {
				duration: 0
			},
			tooltipClass: 'tooltip'
		});
	});
	$('.inGame[game]').click(function () {
		var game = $(this).attr('game');
		var member = $(this).parent().parent().parent().find('.name').attr('member');
		extendData.member = member;
		extendData.game = game;
		clickMenu('extend');
	});
}

function extendSheet() {
	$('#mainCommands .button').removeClass('active');
	var lastDate = 'неизвестно';
	if (typeof(members[extendData.member].games[extendData.game]) == 'string')
		var date = members[extendData.member].games[extendData.game];
	if (typeof(members[extendData.member].games[extendData.game]) == 'object' && 'end' in members[extendData.member].games[extendData.game])
		date = members[extendData.member].games[extendData.game].end;
	if (date != undefined)
		lastDate = (new Date(date.substr(0, 10))).toString('fulltime').slice(0, -7) + date.substr(10);

	$('#nameGameData .name').text(members[extendData.member].name);
	$('#nameGameData .game').html('<img src="images/logo/logo_' + extendData.game + '.png" />');
	$('#dateUpdated .date').text(lastDate);
	$('#extendData .infoRow').hide();
	$('#extendData .infoRow.' + extendData.game + ', #extendData .infoRow.all').show();
	buttonExtend();

}

function buttonExtend() {
	var menu = $('#extendMenu .button.active').attr('menu');
	$('#extendSheet').attr('view', menu);
	if (menu == 'all') {
		$('#extendPeriod').hide();
		$('#Difference').hide();
		resize();
		wait($('#extendSheet'));
		if (checkMemberStat(members[extendData.member], extendData.game, (new Date()), outExtend))
			wait($('#extendSheet'), false);
	}
	;
	if (menu == 'period') {
		$('#extendPeriod').show();
		$('#Difference').show();
		resize();
		var date = checkDate($('#extendPeriod'));
		wait($('#extendSheet'));
		if (checkMemberStat(members[extendData.member], extendData.game, date[0], outExtend))
			wait($('#extendSheet'), false);
	}
	;
}

function outExtend() {
	wait($('#extendSheet'), false);
	$('#extendData').find('.infoRow>span:last-child').html('-');
	$('#Medals').find('.infoBlock>span').html('');
	$('#tabTanks').find('.table').html('');
	var menu = $('#extendMenu').find('.button.active').attr('menu');
	var stat = members[extendData.member].getStat(extendData.game, new Date());
	if (menu == 'period') {
		var date = checkDate($('#extendPeriod'));
		var stat1 = members[extendData.member].getStat(extendData.game, date[1]);
		var stat2 = members[extendData.member].getStat(extendData.game, date[0]);
		stat = new Stat(extendData.game, stat1, stat2);
	}
	if (stat != undefined && stat.main.battles != 0) {
		for (var variable in stat.main) {
			var text = stat.main[variable];
			if (menu == 'period') {
				text = stat1.main[variable];
				if (stat.main[variable] != 0)
					text += ' (+' + stat.main[variable] + ')'
			}
			$('#Result').find('.infoRow[param="' + variable + '"]>span:last-child').html(text);
		}
		for (variable in stat.ext) {
			var postNum = '';
			if (variable == 'wins' || variable == 'losses' || variable == 'survived' || variable == 'hits')
				postNum = '%';
			text = toStr(stat.ext[variable], 2) + postNum;
			if (variable == 'wins')
				text = '<span x="' + stat.main.wins + '" y="' + stat.main.battles + '" class="effWR" style="color:' + effColor(extendData.game, stat.ext.wins, 'WR') + '">' + text + '</span>';
			if (variable.substr(0, 3) == 'eff') {
				text = '<span class="eff" style="color:' + effColor(extendData.game, stat.ext[variable], variable.substr(3)) + '" type="' + variable.substr(3) + '" value="' + stat.ext[variable] + '">' + text + '</span>';
			}
			if (menu == 'period') {
				text = toStr(stat1.ext[variable], 2) + postNum;
				if (variable == 'wins')
					text = '<span x="' + stat1.main.wins + '" y="' + stat1.main.battles + '" class="effWR" style="color:' + effColor(extendData.game, stat1.ext.wins, 'WR') + '">' + text + '</span>';
				if (variable.substr(0, 3) == 'eff') {
					text = '<span class="eff" style="color:' + effColor(extendData.game, stat1.ext[variable], variable.substr(3)) + '" type="' + variable.substr(3) + '" value="' + stat1.ext[variable] + '">' + text + '</span>';
				}
				var diff = stat1.ext[variable] - stat2.ext[variable];
				if (diff != 0)
					text += ' (' + toStr(diff, 2, true) + postNum + ')';
			}
			$('#Effect .infoRow[param="' + variable + '"]>span:last-child').html(text);
		}
		var battles = toStr(Math.round(100 * stat.main.battles / ((new Date()).toInt() - members[extendData.member].regDate.toInt())) / 100, 2);
		if (menu == 'period')
			battles = toStr(Math.round(100 * stat.main.battles / (date[1].toInt() - date[0].toInt())) / 100, 2);
		$('#Effect .infoRow[param="battles"]>span:last-child').html(battles);
		if (menu == 'period') {
			for (variable in stat.ext) {
				postNum = '';
				if (variable == 'wins' || variable == 'losses' || variable == 'survived' || variable == 'hits')
					postNum = '%';
				text = toStr(stat.ext[variable], 2) + postNum;
				;
				if (variable == 'wins')
					text = '<span x="' + stat.main.wins + '" y="' + stat.main.battles + '" class="effWR" style="color:' + effColor(extendData.game, stat.ext.wins, 'WR') + '">' + text + '</span>';
				if (variable.substr(0, 3) == 'eff') {
					text = '<span style="color:' + effColor(extendData.game, stat.ext[variable], variable.substr(3)) + '">' + text + '</span>';
				}
				;
				if (variable in stat.main && stat.main[variable] != 0)
					text = stat.main[variable] + ' (' + text + ')';
				if (variable == 'damage' && stat.ext.damage != 0 && (extendData.game == 'wot' || extendData.game == 'wotb'))
					text = stat.main.damageD + ' (' + text + ')';
				$('#Difference .infoRow[param="' + variable + '"]>span:last-child').html(text);
			}
			;
			$('#Difference .infoRow[param="battles"]>span:last-child').html(stat.main.battles);
		}
		if ('medals' in stat) {
			if ('medalSections' in games[extendData.game].data) {
				var sections = new Array();
				for (var section in games[extendData.game].data.medalSections) {
					if (typeof(games[extendData.game].data.medalSections[section]) == 'object') {
						var order = games[extendData.game].data.medalSections[section].order;
						sections[order] = new Object();
						sections[order].name = section;
						sections[order].nameRu = games[extendData.game].data.medalSections[section].name;
					} else {
						sections[section] = new Object();
						sections[section].name = section;
						sections[section].nameRu = games[extendData.game].data.medalSections[section];
					}
				}
				;
				for (var number in sections) {
					var listMedals = new Array();
					for (var medal in games[extendData.game].medals) {
						if (games[extendData.game].medals[medal].section == sections[number].name) {
							listMedals[games[extendData.game].medals[medal].myOrder] = medal;
						}
						;
					}
					;
					var sect = $('<div class="medalSection"></div>').appendTo($('#Medals .infoBlock>span'));
					sect.append('<div class="nameGroupMedals">' + sections[number].nameRu + '</div>');
					var medalSect = $('<div class="medals"></div>').appendTo($(sect));
					for (var num in listMedals) {
						medal = listMedals[num];
						if (medal in stat.medals) {
							var thatMedal = $('<div class="medal" medal="' + medal + '"></div>').appendTo(medalSect);
							var src = games[extendData.game].medals[medal].image;
							var value = '<span>' + stat.medals[medal] + '</span>';
							if (games[extendData.game].medals[medal].options != null) {
								src = games[extendData.game].medals[medal].options[stat.medals[medal] - 1].image;
								thatMedal.attr('value', stat.medals[medal] - 1);
								value = '';
							}
							;
							thatMedal.append('<img src="' + src + '" />');
							if (stat.medals[medal] > 1)
								thatMedal.append(value);
						}
						;
					}
					;
					if (medalSect.html() == '')
						sect.remove();
				}
				;
			} else {
				var listMedals = new Array();
				for (var medal in games[extendData.game].medals) {
					listMedals[games[extendData.game].medals[medal].myOrder] = medal;
				}
				;
				for (num in listMedals) {
					medal = listMedals[num];
					if (medal in stat.medals) {
						var thatMedal = $('<div class="medal" medal="' + medal + '"></div>').appendTo($('#Medals .infoBlock>span'));
						thatMedal.append('<img src="' + games[extendData.game].medals[medal]['image'] + '" />');
						if (stat.medals[medal] > 1)
							thatMedal.append('<span>' + stat.medals[medal] + '</span>');
					}
					;
				}
				;
			}
			;
			$('#Medals').find('.medal').each(function () {
				var game = extendData.game;
				var medal = $(this).attr('medal');
				showMedalTooltip(game, medal, this);
			});
		}
		if ('technics' in stat) {
			$('#tabTanks .table').html('');

			var table = new Array();
			var row = 0;
			for (var technic in stat.technics) {
				var battles = stat.technics[technic][0] || 0;
				var wins = stat.technics[technic][1] || 0;
				var master = stat.technics[technic][2] || 0;

				var tech = games[extendData.game].technics[technic];

				table[row] = new Array();

				table[row]['name'] = tech.name;
				table[row]['type'] = clanData.sortTypes.indexOf(tech.type);
				table[row]['prem'] = tech.isPrem;
				table[row]['level'] = tech.level.toInt();
				table[row]['nation'] = clanData.sortNations.indexOf(tech.nation);

				table[row]['battles'] = battles;
				if (menu == 'period')
					table[row]['sbattles'] = '<span>' + stat1.technics[technic][0] + (stat1.technics[technic][0] == battles ? '' : (' (+' + battles + ')')) + '</span>';
				else
					table[row]['sbattles'] = '<span>' + battles + '</span>';
				table[row]['wins'] = Math.round(10000 * wins / battles) / 100;
				if (table[row]['wins'] != 0)
					table[row]['swins'] = '<span>' + wins + ' (<span x="' + wins + '" y="' + battles + '" class="effWR" style="color:' + effColor(extendData.game, table[row]['wins'], 'WR') + ';">' + toStr(table[row]['wins'], 1) + '%</span>)</span>';
				else
					table[row]['swins'] = '<span style="color:#FE0E00;">0</span>'
				if (menu == 'period') {
					table[row]['pwins'] = Math.round(10000 * stat1.technics[technic][1] / stat1.technics[technic][0]) / 100;
					if (!(technic in stat2.technics) || stat2.technics[technic][0] == 0)
						diff = 0;
					else
						diff = table[row]['pwins'] - Math.round(10000 * stat2.technics[technic][1] / stat2.technics[technic][0]) / 100;
					table[row]['spwins'] = '<span>' + toStr(table[row]['pwins'], 2) + '%</span>';
					if (diff != 0)
						table[row]['spwins'] = '<span>' + table[row]['spwins'] + ' (<span style="color:' + (diff > 0 ? '#60FF00' : '#FE0E00') + ';">' + toStr(diff, 2, true) + '%</span>)</span>';
				}
				;
				table[row]['master'] = master;
				if (master != 0) {
					var src = imgClassTank(stat.technics[technic][2]);
					if (menu == 'period')
						src = imgClassTank(stat1.technics[technic][2]);
					table[row]['smaster'] = '<span><img src="' + src + '" /></span>';
				} else
					table[row]['smaster'] = '';

				var style = '';
				var name = tech.name;
				if (extendData.game == 'wot') {
					name = tech.shortName;
					style = "background-position: -5px center;";
				}
				;
				if (extendData.game == 'wotb')
					style = "background-position: -40px center; background-size: 150px;";
				if (extendData.game == 'wowp') {
					name = tech.nameRu;
				}
				;
				if (extendData.game == 'wows') {
					style = "background-position: left center; background-size: 80px;";
				}
				;
				table[row]['technic'] = '<div class="nation"><img src="images/nations/' + tech.nation + '.png" /><span><span>' + romanNum[tech.level] + '</span></span></div><div class="technic" technic="' + tech.id + '" style="background-image: url(\'' + tech.image + '\'); ' + style + '"><span class="' + (tech.isPrem == 1 ? 'prem' : '') + '">' + name + '</span></div>';

				row++;
			}
			;
			table.sort(function (a, b) {
				var res = 0;
				if (a['name'] > b['name'])
					res = 1;
				if (a['name'] < b['name'])
					res = -1;
				if (a['type'] > b['type'])
					res = 1;
				if (a['type'] < b['type'])
					res = -1;
				if (a['prem'] > b['prem'])
					res = 1;
				if (a['prem'] < b['prem'])
					res = -1;
				if (a['nation'] > b['nation'])
					res = 1;
				if (a['nation'] < b['nation'])
					res = -1;
				if (a['level'] - 1 > b['level'] - 1)
					res = -1;
				if (a['level'] - 1 < b['level'] - 1)
					res = 1;
				return res;
			});
			for (row in table)
				table[row]['row'] = parseInt(row);
			var tableHead = new Array();
			tableHead[0] = ['Техника', 'technic', 'row', 250];
			tableHead[1] = ['Боёв', 'sbattles', 'battles', 80, -1, 'center'];
			tableHead[2] = ['Побед', 'swins', 'wins', 120, -1, 'center'];
			if (extendData.game == 'wot' || extendData.game == 'wotb')
				tableHead[3] = ['Знак классности', 'smaster', 'master', 80, -1, 'center'];
			if (menu == 'period') {
				tableHead[1] = ['Боёв', 'sbattles', 'battles', 120, -1, 'center'];
				if ('3' in tableHead)
					tableHead[4] = ['Знак классности', 'smaster', 'master', 80, -1, 'center'];
				tableHead[3] = ['Процент побед', 'spwins', 'pwins', 160, -1, 'center'];
			}
			if (table.length)
				$('#tabTanks .table').table({
					header: tableHead,
					data: table,
					lineHeight: 44
				});
			else
				$('#tabTanks .table').css({
					'max-height': 0,
					'min-height': 0
				});
		} else
			$('#tabTanks .table').css({
				'max-height': 0,
				'min-height': 0
			});
	}
	$('.effWR').each(function () {
		var x = $(this).attr('x');
		var y = $(this).attr('y');
		var p1 = Math.floor(100 * x / y) + 1;
		var p2 = 5 * (Math.floor(100 * x / y / 5) + 1);
		var p3 = 10 * (Math.floor(100 * x / y / 10) + 1);
		var a1 = Math.floor((p1 / 100 * y - x) / (1 - p1 / 100)) + 1;
		var a2 = Math.floor((p2 / 100 * y - x) / (1 - p2 / 100)) + 1;
		var a3 = Math.floor((p3 / 100 * y - x) / (1 - p3 / 100)) + 1;
		var s = 'До <span style="color:' + effColor('', p1, 'WR') + '">' + p1 + '%</span> - ' + a1 + ' ' + getWordEnding(a1)[3];
		if (p1 != p2)
			s += '<br />До <span style="color:' + effColor('', p2, 'WR') + '">' + p2 + '%</span> - ' + a2 + ' ' + getWordEnding(a2)[3];
		if (p2 != p3)
			s += '<br />До <span style="color:' + effColor('', p3, 'WR') + '">' + p3 + '%</span> - ' + a3 + ' ' + getWordEnding(a3)[3];
		if (p1 < 100) {
			$(this).attr('title', '').tooltip({
				content: s,
				position: {
					my: "left+6 top-4",
					at: "right top"
				},
				show: {
					duration: 0
				},
				hide: {
					duration: 0
				},
				tooltipClass: 'tooltip'
			});
		}
		;
	});
	$('.eff').each(function () {
		var s = '';
		var eff = $(this).attr('type').toLowerCase();
		var value = 1 * $(this).attr('value');
		if (value != undefined && value != null && value != 0) {
			for (var num in games[extendData.game].data.effectColor[eff]) {
				if (value >= games[extendData.game].data.effectColor[eff][num]['value'])
					s = '<strong>' + games[extendData.game].data.effectColor[eff][num]['description'] + '</strong>';
				else {
					var str = toStr((games[extendData.game].data.effectColor[eff][num]['value'] - value), 1);
					s += '<br />До следующего ранга осталось <strong>' + str + '</strong>';
					break;
				}
				;
			}
			;
		}
		;
		$(this).parent().attr('title', '').tooltip({
			content: s,
			position: {
				my: "left top",
				at: "right top"
			},
			hide: {
				duration: 0
			},
			tooltipClass: 'tooltip'
		});
	});
}

function showMedalTooltip(game, medal, object) {
	var name = games[game].medals[medal].nameRu;
	var description = games[game].medals[medal].description;
	if (games[game].medals[medal].options != null) {
		var value = $(object).attr('value');
		name = games[game].medals[medal].options[value].name;
	}
	;
	var s = '<b>' + name + '</b><br />' + description;
	$(object).attr('title', '').tooltip({
		content: s,
		position: {
			my: "left top",
			at: "right top"
		},
		hide: {
			duration: 0
		},
		tooltipClass: 'tooltip'
	});
}

function checkMemberStat(member, game, date, func) {
	date.setDate(1);
	if (game in member.games) {
		var load = true;
		var check = true;
		if (member.games[game].load != undefined) {
			if (member.games[game].init != undefined && member.games[game].init <= date)
				check = false;
			if (member.games[game].load <= date)
				load = false;
		}
		;
		if (check) {
			if (load) {
				var endDate = new Date();
				if (member.games[game].load != undefined) {
					endDate = new Date(member.games[game].load);
					endDate.setDate(endDate.getDate() - 1);
				}
				;
				myQuery('ajax/getStat.php', {
					member: member.id,
					game: game,
					init: date.toString(),
					end: endDate.toString()
				}, function (answer) {
					member.setStat(game, answer.data, answer.start);
					if ($('#extendPeriod').attr('start') != answer.start) {
						$('#extendPeriod').attr('start', answer.start);
						checkDate($('#extendPeriod'));
					}
					;
					func();
				});
				return false;
			} else {
				if ($('#extendPeriod').attr('start') != member.games[game].init) {
					$('#extendPeriod').attr('start', member.games[game].init);
					checkDate($('#extendPeriod'));
				}
				;
				func();
				return false;
			}
			;
		} else
			return true;
	} else
		return true;
}

function adminSheet() {
	buttonAdmin();
}

function buttonAdmin() {
	var menu = $('#adminMenu .button.active').attr('menu');
	$('.adminBlock').hide();
	if (menu == 'visits') {
		$('#adminVisitors').show();
		adminVisitorsCheckDate();
	}
	;
	if (menu == 'events') {
		$('#adminEvents').show();
		adminEventsCheckDate();
	}
	;
	if (menu == 'technics') {
		$('#adminTechnics').show();
		buttonAdminTechnics();
	}
	;
	if (menu == 'medals') {
		$('#adminMedals').show();
		buttonAdminMedals();
	}
	;
	if (menu == 'members') {
		$('#adminMembers').show();
		adminMembers();
	}
	;
	resize();
}

function adminVisitorsCheckDate() {
	$('#adminTableVisitors .table').html('');
	var date = checkDate($('#adminVisitorsMenu')).toString();
	var visits = visitors[date];
	var table = new Array();
	var row = 0;
	for (var visit in visits) {
		table[row] = new Array();
		table[row]['member'] = visits[visit].member == null ? '' : visits[visit].member;
		if (visits[visit].ip != null)
			table[row]['ip'] = '<span>' + visits[visit].ip + '</span>';
		var nameClass = ' name';
		if (memberID == table[row]['member'])
			nameClass += ' myself';
		if (table[row]['member'] in members)
			table[row]['smember'] = '<span class="member' + nameClass + '" member="' + table[row]['member'] + '">' + members[table[row]['member']].name + '</span>';
		else
			table[row]['smember'] = '<span class="member' + nameClass + '">' + table[row]['member'] + '</span>';
		table[row]['cookie'] = '<span>' + visits[visit].cookie + '</span>';
		if (visits[visit].browser != null) {
			var browser = detect.parse(visits[visit].browser);
			table[row]['browser'] = '<span>' + browser.device.type + ', ' + browser.os.name + ', ' + browser.browser.name + '</span>';
		}
		;
		if (visits[visit].time.length > 1)
			table[row]['time'] = '<span class="visits">' + visits[visit].time.length + ' ' + getWordEnding(visits[visit].time.length)[4] + '</span>';
		else
			table[row]['time'] = '<span>' + visits[visit].time[0] + '</span>';
		row++;
	}
	;
	var tableHead = new Array();
	tableHead[0] = ['Посетитель', 'smember', 'member', 140, 1, 'left'];
	tableHead[1] = ['IP адрес', 'ip', 'ip', 100, 1, 'center'];
	tableHead[2] = ['Кука', 'cookie', 'cookie', 240, 1, 'center'];
	tableHead[3] = ['Браузер', 'browser', '', 300, , 'left'];
	tableHead[4] = ['Время', 'time', 'time', 100, 1, 'center'];
	$('#adminTableVisitors .table').table({
		header: tableHead,
		data: table,
		lineHeight: 50
	});

	$('#adminTableVisitors .table .visits').each(function () {
		var row = parseInt($(this).parent().parent().attr('row'));
		var date = checkDate($('#adminVisitorsMenu')).toString();
		var times = visitors[date][row].time;
		var list = '';
		for (var time in times)
			list += '<div class="time">' + times[time] + '</div>';
		$(this).parent().attr('title', '').tooltip({
			content: list,
			disabled: false,
			position: {
				my: 'left top',
				at: 'right top'
			},
			hide: {
				duration: 0
			},
			tooltipClass: 'tooltip'
		});
	});
}

function adminEventsCheckDate() {
	var date = checkDate($('#adminEventsMenu')).toString();
}

function buttonAdminTechnics() {
	resize();
	var game = $('#adminTechnicsMenu .button.active').attr('menu');
	$('#adminTechnicsSelectors [selector="nations"]').html('');
	for (var num in clanData.sortNations) {
		var nation = clanData.sortNations[num]
		if (nation in games[game].data.technicNations)
			$('#adminTechnicsSelectors [selector="nations"]').append('<div class="select" nation="' + nation + '"><img src="images/nations/' + nation + '.png" /><div class="text">' + games[game].data.technicNations[nation] + '</div></div>');
	}
	;
	$('#adminTechnicsSelectors [selector="types"]').html('');
	for (var num in clanData.sortTypes) {
		var type = clanData.sortTypes[num]
		if (type in games[game].data.technicTypes)
			$('#adminTechnicsSelectors [selector="types"]').append('<div class="select" type="' + type + '"><img src="images/types/' + type + '.png" /><div class="text">' + games[game].data.technicTypes[type] + '</div></div>');
	}
	;
	$('#adminTechnicsSelectors [selector="levels"]').html('');
	for (var level = 1; level <= 10; level++) {
		$('#adminTechnicsSelectors [selector="levels"]').append('<div class="select" level="' + level + '"><div class="text">' + level + '<br />уровень</div></div>');
	}
	;
	$('#adminTechnicsSelectors [selector="prem"]').html('<div class="select" stat="prem"><div class="text">Премиум</div></div>');
	$('#adminTechnicsSelectors [selector="stats"]').html('<div class="select" stat="new"><div class="text">Новые</div></div><div class="select" stat="chn"><div class="text">Измененные</div></div><div class="select" stat="del"><div class="text">Удаленные</div></div><div class="select" stat="clear"><div class="text">Сбросить фильтр</div></div>');

	$('#adminTechnicsSelectors .select').click(function () {
		if ($(this).hasClass('selected'))
			$(this).removeClass('selected');
		else
			$(this).addClass('selected');
		if ($('#adminTechnicsSelectors .select[stat="clear"]').hasClass('selected'))
			$('#adminTechnicsSelectors .select').removeClass('selected');
		adminTechniksList();
	});
	$('#adminTechnicsSelectors [selector="confirm"]>div').hide();
	adminTechniksList();
}

function buttonAdminMedals() {
	resize();
	var game = $('#adminMedalsMenu .button.active').attr('menu');
	$('#adminMedalsSelectors [selector="section"]').html('');
	if (game != 'wows') {
		var list = new Array();
		for (var section in games[game].data.medalSections) {
			if (game == 'wowp') {
				var item = section;
				list[item] = {
					name: '',
					section: ''
				};
				list[item]['name'] = games[game].data.medalSections[section];
			} else {
				item = games[game].data.medalSections[section]['order'];
				list[item] = {
					name: '',
					section: ''
				};
				list[item].name = games[game].data.medalSections[section].name;
			}
			;
			list[item].section = section;
		}
		;
		for (var item in list) {
			$('#adminMedalsSelectors [selector="section"]').append('<div class="select" section="' + list[item]['section'] + '"><div class="text">' + list[item]['name'] + '</div></div>');
		}
		;
	}
	;
	$('#adminMedalsSelectors [selector="type"]').html('');
	if (game == 'wot' || game == 'wows') {
		var list = new Array();
		for (var medal in games[game].medals) {
			if (list.indexOf(games[game].medals[medal].type) == -1)
				list.push(games[game].medals[medal].type);
		}
		;
		for (var type in medalTypes) {
			if (list.indexOf(type) != -1)
				$('#adminMedalsSelectors [selector="type"]').append('<div class="select" type="' + type + '"><div class="text">' + medalTypes[type] + '</div></div>');
		}
		;
	}
	;
	$('#adminMedalsSelectors [selector="stats"]').html('<div class="select" stat="new"><div class="text">Новые</div></div><div class="select" stat="chn"><div class="text">Измененные</div></div><div class="select" stat="del"><div class="text">Удаленные</div></div><div class="select" stat="clear"><div class="text">Сбросить фильтр</div></div>');

	$('#adminMedalsSelectors .select').click(function () {
		if ($(this).hasClass('selected'))
			$(this).removeClass('selected');
		else
			$(this).addClass('selected');
		if ($('#adminMedalsSelectors .select[stat="clear"]').hasClass('selected'))
			$('#adminMedalsSelectors .select').removeClass('selected');
		adminMedalsList();
	});
	$('#adminMedalsSelectors [selector="confirm"]>div').hide();
	adminMedalsList();
}

function adminTechniksList() {
	var game = $('#adminTechnicsMenu .button.active').attr('menu');
	$('#adminTableTechnics .table').html('').removeAttr('style');
	$('#adminTechnicsSelectors [selector="confirm"]>div').hide();
	var select = {
		nation: new Object(),
		type: new Object(),
		level: new Object(),
		prem: false,
		stat: {
			new: false,
			chn: false,
			del: false
		}
	};
	var selectTypes = {
		nation: true,
		type: true,
		level: true,
		prem: true,
		stat: true
	};
	for (var nation in games[game].data.technicNations)
		select.nation[nation] = false;
	for (var type in games[game].data.technicTypes)
		select.type[type] = false;
	for (var level = 1; level <= 10; level++)
		select.level[level] = false;
	var selection = $('#adminTechnicsSelectors .select.selected');
	var list = false;
	if (selection.length)
		for (var num = 0; num < selection.length; num++) {
			list = true;
			var elem = selection.eq(num);
			var typeSelection = elem.parent().attr('selector');
			if (typeSelection == 'nations') {
				select.nation[elem.attr('nation')] = true;
				selectTypes.nation = false;
			}
			if (typeSelection == 'types') {
				select.type[elem.attr('type')] = true;
				selectTypes.type = false;
			}
			if (typeSelection == 'levels') {
				select.level[elem.attr('level')] = true;
				selectTypes.level = false;
			}
			if (typeSelection == 'prem') {
				select.prem = true;
				selectTypes.prem = false;
			}
			if (typeSelection == 'stats') {
				select.stat[elem.attr('stat')] = true;
				selectTypes.stat = false;
			}
		}
	;
	if (list) {
		list = new Array();
		var typesChange = new Array();
		for (var tech in games[game].technics) {
			if ((select.nation[games[game].technics[tech].nation] || selectTypes.nation) && (select.type[games[game].technics[tech].type] || selectTypes.type) && (select.level[games[game].technics[tech].level] || selectTypes.level) && (select.prem == games[game].technics[tech].isPrem || selectTypes.prem) && (select.stat[games[game].technics[tech].state] || selectTypes.stat)) {
				list[tech] = games[game].technics[tech];
			}
			;
		}
		;
		var table = new Array();
		var row = 0;
		for (var technic in list) {
			table[row] = new Array();
			table[row]['id'] = technic;
			table[row]['sid'] = '<span>' + technic + '</span>';
			table[row]['nation'] = clanData.sortNations.indexOf(list[technic].nation);
			table[row]['snation'] = '<span><img src="images/nations/' + list[technic].nation + '-small.png"></span>';
			table[row]['level'] = list[technic].level.toInt();
			table[row]['slevel'] = '<span>' + romanNum[list[technic].level] + '</span>';
			table[row]['type'] = clanData.sortTypes.indexOf(list[technic].type);
			table[row]['stype'] = '<span>' + games[game].data.technicTypes[list[technic].type] + '</span>';
			table[row]['prem'] = list[technic].isPrem;
			var name = list[technic].name;

			table[row]['name'] = name;
			table[row]['technic'] = list[technic].type;
			var style = '';
			if (game == 'wot') {
				name = list[technic].shortName;
				style = "background-position: -5px center;";
			}
			;
			if (game == 'wotb')
				style = "background-position: -40px center; background-size: 150px;";
			if (game == 'wowp') {
				name = list[technic].nameRu;
			}
			;
			if (game == 'wows') {
				style = "background-position: left center; background-size: 80px;";
			}
			;
			table[row]['technic'] = '<div class="technic" technicID="' + list[technic].id + '" style="background-image: url(\'' + list[technic].image + '\'); ' + style + '"><span class="' + (list[technic].isPrem == 1 ? 'prem' : '') + '">' + name + '</span></div>';
			if (list[technic].state == 'new') {
				table[row]['lineClass'] = 'bg-green';
				$('#adminTechnicsSelectors [confirm="new"]').show();
			}
			;
			if (list[technic].state == 'chn') {
				table[row]['lineClass'] = 'bg-yellow';
				$('#adminTechnicsSelectors [confirm="chn"]').show();
				for (var type in games[game].technicChanges[technic]) {
					if (type != 'id' && games[game].technicChanges[technic][type] != undefined) {
						if (!(type in typesChange))
							typesChange[type] = 1;
						else
							typesChange[type]++;
					}
					;
				}
				;
			}
			;
			if (list[technic].state == 'old')
				table[row]['lineClass'] = 'bg-red';
			row++;
		}
		;
		$('#adminTechnicsSelectors .confirm[confirm="chn"]>div>span').text('');
		var countTypesChanges = Object.keys(typesChange).length;
		if (countTypesChanges) {
			if (countTypesChanges == 1)
				for (type in typesChange) {
					$('#adminTechnicsSelectors .confirm[confirm="chn"]>div>span').html(nameOfType(type));
					$('#adminTechnicsSelectors .confirm[confirm="chn"]').attr('title', '').tooltip({
						disabled: true
					});
				}
			else {
				var changes = '';
				for (type in typesChange)
					changes += '<div class="nameChange">' + nameOfType(type) + '</div>';
				$('#adminTechnicsSelectors .confirm[confirm="chn"]').attr('title', '').tooltip({
					content: changes,
					disabled: false,
					position: {
						my: 'left top',
						at: 'right top'
					},
					hide: {
						duration: 0
					},
					tooltipClass: 'tooltip'
				});
			}
			;
		}
		;
		table.sort(function (a, b) {
			var res = 0;
			if (a['name'] > b['name'])
				res = 1;
			if (a['name'] < b['name'])
				res = -1;
			if (a['type'] > b['type'])
				res = 1;
			if (a['type'] < b['type'])
				res = -1;
			if (a['prem'] > b['prem'])
				res = 1;
			if (a['prem'] < b['prem'])
				res = -1;
			if (a['level'] - 1 < b['level'] - 1)
				res = -1;
			if (a['level'] - 1 > b['level'] - 1)
				res = 1;
			if (a['nation'] > b['nation'])
				res = 1;
			if (a['nation'] < b['nation'])
				res = -1;
			return res;
		});
		var tableHead = new Array();
		tableHead[0] = ['ID техники', 'sid', 'id', 84, , 'center'];
		tableHead[1] = ['Нация', 'snation', 'nation', 60, , 'center'];
		tableHead[2] = ['Уровень', 'slevel', 'level', 80, , 'center'];
		tableHead[3] = ['Тип', 'stype', 'type', 100, , 'center'];
		tableHead[4] = ['Техника', 'technic', '', 200];
		if (table.length)
			$('#adminTableTechnics .table').table({
				header: tableHead,
				data: table,
				lineHeight: 50
			});
		else
			$('#adminTableTechnics .table').css({
				'max-height': 0
			});

		if (countTypesChanges) {
			table = $('#adminTableTechnics .table .row');
			for (var row = 0; row < table.length; row++) {
				var technicID = $(table[row]).find('.technic').attr('technicID');
				if (games[game].technics[technicID].state == 'chn') {
					var changes = '';
					for (type in games[game].technicChanges[technicID]) {
						if (type != 'id' && games[game].technicChanges[technicID][type] != null) {
							if (type == 'image') {
								style == '';
								if (game == 'wotb')
									style = 'width: 100%; height: 100%';
								changes += '<div class="change"><span class="nameChange">Изображение</span><span><span class="oldValue"><img src="' + games[game].technics[technicID].image + '" style="' + style + '" /></span></span><span><span class="newValue"><img src="' + games[game].technicChanges[technicID].image + '" style="' + style + '" /></span></span></div>';
							} else {
								changes += '<div class="change"><span class="nameChange">' + nameOfType(type) + '</span><span><span class="oldValue">' + (type == 'level' ? romanNum[games[game].technics[technicID][type]] : games[game].technics[technicID][type]) + '</span></span><span><span class="newValue">' + (type == 'level' ? romanNum[games[game].technicChanges[technicID][type]] : games[game].technicChanges[technicID][type]) + '</span></span></div>';
							}
						}
						;
					}
					;
					$(table[row]).attr('title', '').tooltip({
						content: changes,
						position: {
							my: 'left top',
							at: 'right top'
						},
						hide: {
							duration: 0
						},
						tooltipClass: 'tooltip'
					});
				}
				;
			}
			;
		}
		;

		$('#adminTableTechnics .table .row').click(function () {
			var technicID = $(this).find('.technic').attr('technicID');
			if (games[game].technics[technicID].state == 'new') {
				var list = new Array();
				list.push(technicID);
				wait($('#adminTableTechnics'));
				myQuery('ajax/setChanges.php', {
					game: game,
					state: 'new',
					type: 'technic',
					list: list
				}, function (answer) {
					wait($('#adminTableTechnics'), false);
					for (var game in answer.data) {
						for (var technic in answer.data[game].technics)
							games[game].technics[technic] = answer.data[game].technics[technic];
					}
					;
					adminTechniksList();
				});
			}
			;
			if (games[game].technics[technicID].state == 'chn') {
				var list = new Array();
				list.push(technicID);
				typesChange = new Array();
				for (type in games[game].technicChanges[technicID]) {
					if (type != 'id' && games[game].technicChanges[technicID][type] != undefined) {
						if (!(type in typesChange))
							typesChange[type] = 1;
						else
							typesChange[type]++;
					}
					;
				}
				;
				var countTypesChanges = Object.keys(typesChange).length;
				if (countTypesChanges == 0) {
					delete games[game].technicChanges[technicID];
					games[game].technics[technicID]['state'] = '';
					adminTechniksList();
				} else {
					wait($('#adminTableTechnics'));
					myQuery('ajax/setChanges.php', {
						game: game,
						state: 'chn',
						type: 'technic',
						list: list,
						change: 'all'
					}, function (answer) {
						wait($('#adminTableTechnics'), false);
						for (var game in answer.data) {
							if ('technics' in answer.data[game])
								for (var technic in answer.data[game].technics)
									games[game].technics[technic] = answer.data[game].technics[technic];
							if ('technicChanges' in answer.data[game])
								for (technic in answer.data[game].technicChanges) {
									if (answer.data[game].technicChanges[technic] == null)
										delete games[game].technicChanges[technic];
									else
										games[game].technicChanges[technic] = answer.data[game].technicChanges[technic];
								}
							;
						}
						;
						adminTechniksList();
					});
				}
				;
			}
			;
		});
	} else
		$('#adminTableTechnics .table').css({
			'max-height': 0
		});
}

function adminMedalsList() {
	var game = $('#adminMedalsMenu .button.active').attr('menu');
	$('#adminTableMedals .table').html('').removeAttr('style');
	$('#adminMedalsSelectors [selector="confirm"]>div').hide();
	var select = {
		section: new Object(),
		type: new Object(),
		stat: {
			new: false,
			chn: false,
			del: false
		}
	};
	var selectTypes = {
		section: true,
		type: true,
		stat: true
	};

	if (game != 'wows')
		for (var section in games[game].data.medalSections)
			select.section[section] = false;
	if (game == 'wot' || game == 'wows')
		for (var type in medalTypes)
			select.type[type] = false;
	var selection = $('#adminMedalsSelectors .select.selected');
	var list = false;
	if (selection.length)
		for (var num = 0; num < selection.length; num++) {
			list = true;
			var elem = selection.eq(num);
			var typeSelection = elem.parent().attr('selector');
			if (typeSelection == 'section') {
				select.section[elem.attr('section')] = true;
				selectTypes.section = false;
			}
			if (typeSelection == 'type') {
				select.type[elem.attr('type')] = true;
				selectTypes.type = false;
			}
			if (typeSelection == 'stats') {
				select.stat[elem.attr('stat')] = true;
				selectTypes.stat = false;
			}
		}
	;
	if (list) {
		list = new Array();
		var typesChange = new Array();
		for (var medal in games[game].medals) {
			if ((select.section[games[game].medals[medal].section] || selectTypes.section) && (select.type[games[game].medals[medal].type] || selectTypes.type) && (select.stat[games[game].medals[medal].state] || selectTypes.stat)) {
				list[medal] = games[game].medals[medal];
			}
			;
		}
		;
		var table = new Array();
		var row = 0;
		for (var medal in list) {
			table[row] = new Array();
			table[row]['id'] = medal;
			table[row]['sid'] = '<span>' + medal + '</span>';
			table[row]['name'] = list[medal].name;
			table[row]['nameRu'] = '<span class="medal in-cell" medal="' + medal + '"><span>' + list[medal].nameRu + '</span></span>';
			table[row]['order'] = parseInt(list[medal].order) || 0;
			table[row]['myOrder'] = parseInt(list[medal].myOrder) || 0;
			if (list[medal].image)
				table[row]['image'] = '<img src="' + list[medal].image + '" width="70" height="70" />';
			else {
				if ('options' in list[medal] && list[medal].options) {
					if (list[medal].options[0].image != null && list[medal].options[0].image != '')
						table[row]['image'] = '<img src="' + list[medal].options[0].image + '" width="70" height="70" />';
					else
						table[row]['image'] = '<img src="images/no-medal-image.png" />';
				} else
					table[row]['image'] = '<img src="images/no-medal-image.png" />';
			}
			;
			table[row]['image'] = '<span>' + table[row]['image'] + '</span>';
			table[row]['description'] = '<span class="in-cell"><span>' + (list[medal].description || 'Нет описания') + '</span></span>';
			if (game == 'wot' || game == 'wotb')
				table[row]['condition'] = '<span class="in-cell"><span>' + (list[medal].condition || 'Нет условий') + '</span></span>';
			table[row]['view'] = list[medal].view == 1 ? '<span class="in-cell view"><img src="images/show.png" /></span>' : '<span class="in-cell view"></span>';
			if (list[medal].state == 'new') {
				table[row]['lineClass'] = 'bg-green';
				$('#adminMedalsSelectors [confirm="new"]').show();
			}
			;
			if (list[medal].state == 'chn') {
				table[row]['lineClass'] = 'bg-yellow';
				$('#adminMedalsSelectors [confirm="chn"]').show();
				for (var type in games[game].medalChanges[medal]) {
					if (type != 'id' && games[game].medalChanges[medal][type] != undefined) {
						if (!(type in typesChange))
							typesChange[type] = 1;
						else
							typesChange[type]++;
					}
					;
				}
				;
			}
			;
			if (list[medal].state == 'old')
				table[row]['lineClass'] = 'bg-red';
			row++;
		}
		;
		$('#adminMedalsSelectors .confirm[confirm="chn"]>div>span').text('');
		var countTypesChanges = Object.keys(typesChange).length;
		if (countTypesChanges) {
			if (countTypesChanges == 1)
				for (type in typesChange) {
					$('#adminMedalsSelectors .confirm[confirm="chn"]>div>span').html(nameOfType(type));
					$('#adminMedalsSelectors .confirm[confirm="chn"]').attr('title', '').tooltip({
						disabled: true
					});
				}
			else {
				var changes = '';
				for (type in typesChange)
					changes += '<div class="nameChange">' + nameOfType(type) + '</div>';
				$('#adminMedalsSelectors .confirm[confirm="chn"]').attr('title', '').tooltip({
					content: changes,
					disabled: false,
					position: {
						my: 'left top',
						at: 'right top'
					},
					hide: {
						duration: 0
					},
					tooltipClass: 'tooltip'
				});
			}
			;
		}
		;
		table.sort(function (a, b) {
			var res = 0;
			if (a.order > b.order)
				res = 1;
			if (a.order < b.order)
				res = -1;
			if (a.myOrder > b.myOrder)
				res = 1;
			if (a.myOrder < b.myOrder)
				res = -1;
			if ('section' in a && 'section' in b) {
				if (a.section > b.section)
					res = 1;
				if (a.section < b.section)
					res = -1;
			}
			;
			return res;
		});
		if (selection.length == 1 && (selection.eq(0).parent().attr('selector') == 'section' || (game == 'wows' && selection.eq(0).parent().attr('selector') == 'type')))
			medalsChangeSort = true;
		else
			medalsChangeSort = false;
		if (medalsChangeSort)
			setMedalSort(table);
		var tableHead = new Array();
		tableHead[0] = ['Номер медали', 'sid', 'id', 60, , 'center'];
		tableHead[1] = ['Имя', 'nameRu', 'name', 100, , 'center'];
		tableHead[2] = ['Изображение', 'image', , 100, , 'center'];
		tableHead[3] = ['Описание', 'description', , 300, , 'left'];
		tableHead[4] = ['Вид', 'view', , 50, , 'center'];
		if (game == 'wot' || game == 'wotb') {
			tableHead[4] = ['Условие', 'condition', , 300, , 'left'];
			tableHead[5] = ['Вид', 'view', , 50, , 'center'];
		}
		;
		if (table.length)
			$('#adminTableMedals .table').table({
				header: tableHead,
				data: table,
				lineHeight: 80
			}, medalsChangeSort ? setMedalSort : '');
		else
			$('#adminTableMedals .table').css({
				'max-height': 0
			});
		$('#adminTableMedals .in-cell').each(function () {
			if (!($(this).parent().parent().hasClass('bg-yellow')))
				if ($(this).width() < $(this).find('span').width() || $(this).height() < $(this).find('span').height()) {
					$(this).parent().attr('title', '').tooltip({
						content: $(this).find('span').html(),
						disabled: false,
						position: {
							my: 'right top',
							at: 'right bottom'
						},
						hide: {
							duration: 0
						},
						tooltipClass: 'tooltip big-size'
					});
				}
			;
		});
		$('#adminTableMedals .in-cell.view').click(function () {
			if (!($(this).parent().parent().hasClass('bg-yellow')))
				var medal = $(this).parent().parent().find('.medal').attr('medal');
			var view = Math.abs(parseInt(games[game].medals[medal].view) - 1);
			var list = new Array();
			list.push(medal);
			wait($('#adminTableMedals'));
			myQuery('ajax/setChanges.php', {
				game: game,
				state: 'chn',
				type: 'medal',
				list: list,
				change: 'view',
				view: view
			}, function (answer) {
				wait($('#adminTableMedals'), false);
				for (var game in answer.data) {
					for (var medal in answer.data[game].medals)
						games[game].medals[medal] = answer.data[game].medals[medal];
				}
				;
				adminMedalsList();
			});

		});
		$('#adminTableMedals .row').each(function () {
			if (!($(this).hasClass('bg-yellow')))
				var medal = $(this).find('.medal').attr('medal');
			if ('options' in games[game].medals[medal] && games[game].medals[medal].options != null) {
				var options = '';
				for (option in games[game].medals[medal].options) {
					options += '<div class="option"><span><span class="image"><img src="' + (games[game].medals[medal].options[option].image || 'images/no-medal-image.png') + '"></span></span><span><span class="text">' + games[game].medals[medal].options[option].name + '</span></span></div>';
				}
				;
				$(this).find('.cell[col="2"]').attr('title', '').tooltip({
					content: options,
					position: {
						my: 'left top',
						at: 'right top'
					},
					hide: {
						duration: 0
					},
					tooltipClass: 'tooltip'
				});
			}
			;
		});
		if (countTypesChanges) {
			table = $('#adminTableMedals .table .row');
			for (var row = 0; row < table.length; row++) {
				var medal = $(table[row]).find('.medal').attr('medal');
				if (games[game].medals[medal].state == 'chn') {
					var changes = '';
					for (type in games[game].medalChanges[medal]) {
						if (type != 'id' && games[game].medalChanges[medal][type] != null) {
							if (type == 'options') {
								changes += '<div class="change"><span class="nameChange">Опции</span><span><span class="oldValue">Старые опции</span></span><span><span class="newValue">Новые опции</span></span></div>';
							} else if (type == 'image') {
								changes += '<div class="change"><span class="nameChange">Изображение</span><span><span class="oldValue"><img class="img-medal" src="' + games[game].medals[medal].image + '" /></span></span><span><span class="newValue"><img class="img-medal" src="' + games[game].medalChanges[medal].image + '" /></span></span></div>';
							} else {
								changes += '<div class="change"><span class="nameChange">' + nameOfType(type) + '</span><span><span class="oldValue">' + games[game].medals[medal][type] + '</span></span><span><span class="newValue">' + games[game].medalChanges[medal][type] + '</span></span></div>';
							}
							;
						}
						;
					}
					;
					$(table[row]).attr('title', '').tooltip({
						content: changes,
						position: {
							my: 'left top',
							at: 'right top'
						},
						hide: {
							duration: 0
						},
						tooltipClass: 'tooltip'
					});
				}
				;
			}
			;
		}
		;
		$('#adminTableMedals .table .row').click(function () {
			var medal = $(this).find('.medal').attr('medal');
			if (games[game].medals[medal].state == 'new') {
				var list = new Array();
				list.push(medal);
				wait($('#adminTableMedals'));
				myQuery('ajax/setChanges.php', {
					game: game,
					state: 'new',
					type: 'medal',
					list: list
				}, function (answer) {
					wait($('#adminTableMedals'), false);
					for (var game in answer.data) {
						for (var medal in answer.data[game].medals)
							games[game].medals[medal] = answer.data[game].medals[medal];
					}
					;
					adminMedalsList();
				});
			}
			;
			if (games[game].medals[medal].state == 'chn') {
				var list = new Array();
				list.push(medal);
				typesChange = new Array();
				for (type in games[game].medalChanges[medal]) {
					if (type != 'id' && games[game].medalChanges[medal][type] != undefined) {
						if (!(type in typesChange))
							typesChange[type] = 1;
						else
							typesChange[type]++;
					}
					;
				}
				;
				var countTypesChanges = Object.keys(typesChange).length;
				if (countTypesChanges == 0) {
					delete games[game].medalChanges[medal];
					games[game].medals[medal]['state'] = '';
					adminMedalsList();
				} else {
					wait($('#adminTableMedals'));
					myQuery('ajax/setChanges.php', {
						game: game,
						state: 'chn',
						type: 'medal',
						list: list,
						change: 'all'
					}, function (answer) {
						wait($('#adminTableMedals'), false);
						for (var game in answer.data) {
							if ('medals' in answer.data[game])
								for (var medal in answer.data[game].medals)
									games[game].medals[medal] = answer.data[game].medals[medal];
							if ('medalChanges' in answer.data[game])
								for (medal in answer.data[game].medalChanges) {
									if (answer.data[game].medalChanges[medal] == null)
										delete games[game].medalChanges[medal];
									else
										games[game].medalChanges[medal] = answer.data[game].medalChanges[medal];
								}
							;
						}
						;
						adminMedalsList();
					});
				}
				;
			}
			;
		});
	}
	;
}

function nameOfType(type) {
	var ret = type;
	if (type in nameType)
		ret = nameType[type];
	return ret;
}

function setMedalSort(table) {
	var game = $('#adminMedalsMenu .button.active').attr('menu');
	var sort = [];
	for (var row in table)
		sort.push(table[row].id);
	myQuery('ajax/setChanges.php', {
		game: game,
		state: 'chn',
		type: 'medal',
		change: 'myOrder',
		sort: sort
	}, function (answer) {
		wait($('#adminTableMedals'), false);
		for (var game in answer.data) {
			if ('medals' in answer.data[game])
				for (var medal in answer.data[game].medals)
					games[game].medals[medal].myOrder = answer.data[game].medals[medal].myOrder;
		}
		;
	});
}

function adminMembers() {
	var row = 0;
	table = new Array();
	for (member in members) {
		table[row] = new Array();
		table[row]['id'] = parseInt(member);
		table[row]['sid'] = '<span>' + member + '</span>';
		table[row]['name'] = members[member].name;
		var nameClass = 'name';
		if (memberID == member)
			nameClass += ' myself';
		table[row]['sname'] = '<span class="' + nameClass + '" member="' + member + '">' + members[member].name + '</span>&nbsp;';
		table[row]['sRealName'] = members[member].realName == null ? '' : '<span>' + members[member].realName + '</span>';
		table[row]['realName'] = members[member].realName == null ? 'яяяя' : members[member].realName;
		table[row]['rights'] = members[member].rights;
		if (table[row]['rights'] == 'sadmin') {
			table[row]['srights'] = 'СуперАдмин';
			table[row]['nrights'] = 0;
		}
		;
		if (table[row]['rights'] == 'admin') {
			table[row]['srights'] = 'Админ';
			table[row]['nrights'] = 1;
		}
		;
		if (table[row]['rights'] == 'member') {
			table[row]['srights'] = 'Участник';
			table[row]['nrights'] = 2;
		}
		;
		if (table[row]['rights'] == 'guest') {
			table[row]['srights'] = 'Гость';
			table[row]['nrights'] = 3;
		}
		;
		table[row]['srights'] = '<span class="userRight">' + table[row]['srights'] + '</span>';
		table[row]['regDate'] = members[member].regDate.toString();
		table[row]['sRegDate'] = '<span>' + members[member].regDate.toString('short') + '</span>';
		if (members[member].clan == null) {
			table[row]['regInClan'] = (new Date()).toString();
			table[row]['sRegInClan'] = '';
			table[row]['permission'] = 'zzzz';
			table[row]['spermission'] = '';
		} else {
			table[row]['regInClan'] = members[member].regInClan.toString();
			table[row]['sRegInClan'] = '<span class="inClan" member="' + member + '">' + members[member].regInClan.toString('short') + '</span>';
			table[row]['permission'] = members[member].role;
			table[row]['spermission'] = '<span>' + roles[members[member].role] + '</span>';
		}
		;
		table[row]['clan'] = members[member].clan == null ? 'zzzz' : clans[members[member].clan].tag;
		table[row]['sclan'] = members[member].clan == null ? '' : '<span class="clan" clan="' + members[member].clan + '">' + clans[members[member].clan].tag + '</span>';
		table[row]['scolor'] = '<span class="memberColor" style="background-color:' + members[member].color + ';"></span>';
		table[row]['del'] = '<span class="delMember"></span>';
		row++;
	}
	;
	var tableHead = new Array();
	tableHead[0] = ['ID', 'sid', 'id', 70, , 'center'];
	tableHead[1] = ['Ник', 'sname', 'name', 140, , 'left'];
	tableHead[2] = ['Реальное имя', 'sRealName', 'realName', 140, , 'left'];
	tableHead[3] = ['Права', 'srights', 'nrights', 90, , 'center'];
	tableHead[4] = ['Дата регистрации', 'sRegDate', 'regDate', 80, , 'center'];
	tableHead[5] = ['Клан', 'sclan', 'clan', 80, , 'center'];
	tableHead[6] = ['Дата в клане', 'sRegInClan', 'regInClan', 80, , 'center'];
	tableHead[7] = ['Должность', 'spermission', 'permission', 160, , 'left'];
	tableHead[9] = ['Цвет', 'scolor', 'regDate', 50, , 'center'];
	tableHead[10] = ['Удалить', 'del', '', 80, , 'center'];
	$('#adminTableMembers .table').table({
		header: tableHead,
		data: table,
		lineHeight: 50
	});
	$('#adminTableMembers .clan').each(function () {
		var clanID = $(this).attr('clan');
		$(this).css({
			color: clans[clanID].color
		});
		var s = '<img src="' + clans[clanID].emblem + '" style="width:32px; height:32px" \> ' + clans[clanID].name;
		$(this).parent().attr('title', '').tooltip({
			content: s,
			position: {
				my: "left top",
				at: "right top"
			},
			hide: {
				duration: 0
			},
			tooltipClass: 'tooltip'
		});
	});
	$('#adminTableMembers .delMember').click(function () {
		var member = $(this).parent().parent().find('.name').attr('member');
		wait($('#adminTableMembers'));
		myQuery('ajax/setMembers.php', {
			state: 'del',
			member: member
		}, function (answer) {
			if (answer.status == 'ok')
				delete members[member];
			adminMembers();
		}, function () {
			wait($('#adminTableMembers'), false);
		});
	});
	$('#adminTableMembers .row .cell[col="2"]').dblclick(function () {
		var name = $(this).text();
		var input = $('<input />').appendTo($(this)).addClass('newName').val(name).attr('initVal', name).focus();
		input.focusout(function () {
			$(this).remove();
		});
		input.keyup(function (e) {
			if (e.which == 13) {
				if ($(this).val() != $(this).attr('initVal')) {
					var member = $(this).parent().parent().find('.name').attr('member');
					var name = $(this).val();
					wait($('#adminTableMembers'));
					myQuery('ajax/setMembers.php', {
						state: 'chn',
						member: member,
						name: name
					}, function (answer) {
						members[member].realName = answer.name;
						$('#adminTableMembers .name[member="' + member + '"]').parent().parent().find('.cell[col="2"]').text(answer.name);
					}, function () {
						wait($('#adminTableMembers'), false);
					});
				}
				;
				$(this).remove();
			}
			;
			if (e.which == 27) {
				$(this).remove();
			}
			;
		});
	});
}

function addMember() {
	$('#formAddMember').show();
	$('#formAddMember .block-find input').focus();
}

function closeAddMember() {
	$('#formAddMember').removeClass('wide');
	$('#formAddMember input').val('');
	$('#formAddMember .listFindedMembers').html('');
	$('#formAddMember').hide();
}

function findMember() {
	var name = $('#formAddMember input').val();
	wait($('#formAddMember'));
	myQuery('ajax/setMembers.php', {
		state: 'find',
		name: name
	}, function (answer) {
		$('#formAddMember').removeClass('wide').removeClass('hight');
		$('#formAddMember .listFindedMembers').html('');
		if (answer.data.length) {
			$('#formAddMember').addClass('wide');
			for (var member in answer.data) {
				var newMember = $('<div></div>').appendTo($('#formAddMember .listFindedMembers'));
				newMember.addClass('newMember').addClass('name');
				newMember.attr('member', answer.data[member].account_id);
				newMember.html('<span>' + answer.data[member].nickname + '<span>');
				var games = $('<span></span>').appendTo(newMember);
				games.addClass('games');
				for (game in answer.data[member].games) {
					$('<span><img src="images/logo/logo_' + answer.data[member].games[game] + '.png" /></span>').appendTo(games);
				}
				;
			}
			;
			$('#formAddMember .newMember').click(function () {
				var member = $(this).attr('member');
				wait($('#formAddMember'));
				myQuery('ajax/setMembers.php', {
					state: 'new',
					member: member
				}, function (answer) {
					if ('member' in answer)
						members[answer.member[0]['id']] = new Member(answer.member[0]);
					if ('clan' in answer)
						clans[answer.clan['id']] = answer.clan;
					closeAddMember();
					adminMembers();
				}, function () {
					wait($('#formAddMember'), false);
				});
			});
		}
		;
	}, function () {
		wait($('#formAddMember'), false);
	});
}

function graphSheet() {
	sheets.graph = viewMembers;
	$('#graphMenuGame .button').removeClass('active');
	$('#graphMenuGame .button[menu="wot"]').addClass('active');
	$('#graphList .table').html('');
	buttonGraphGame();
}

function buttonGraphGame() {
	var game = $('#graphMenuGame .button.active').attr('menu');
	$('#graphMenuParam .button').removeClass('active').hide();
	$('#graphMenuParam .button.' + game + ', #graphMenuParam .button.all').show();
	$('#graphMenuParam .button[menu="wins"]').addClass('active');
	buttonGraph();
}

function buttonGraph() {
	checkDate($('#graphPeriod'));
	graphPeriod();
}

function graphPeriod() {
	var game = $('#graphMenuGame .button.active').attr('menu');
	var list = new Array();
	for (member in members) {
		if (members[member].clan == clanData.id || viewMembers == 'all') {
			if (game in members[member].games && members[member].games[game] != undefined && members[member].games[game] != null) {
				var data = new Object();
				data['member'] = member;
				data['visible'] = true;
				list.push(data);
				if (members[member].games[game].load != undefined) {
					var stat = members[member].getStat(game);
					if (stat != undefined && stat.main.battles == 0)
						delete list[member];
				}
				;
			}
			;
		}
		;
	}
	;
	if ($('#graphList .table').html() != '') {
		$('#graphList .table .row').each(function () {
			var member = $(this).find('.name').attr('member');
			var height = $(this).height();
			var top = parseInt($(this).css('top'));
			var row = parseInt($(this).attr('row'));
			var order = row + Math.round(top / height);
			var visible = true;
			if ($(this).hasClass('hide'))
				visible = false;
			for (var item in list) {
				if (list[item].member == member) {
					list[item].order = order;
					list[item].visible = visible;
					break;
				}
				;
			}
			;
		});
		list.sort(function (a, b) {
			var res = 0;
			if (a['order'] > b['order'])
				res = 1;
			if (a['order'] < b['order'])
				res = -1;
			return res;
		});
	}
	;
	wait($('#graphSheet'));
	checkGraphMembers(list, game, 0);
}

function checkGraphMembers(list, game, item) {
	if (item >= list.length)
		graphList(list);
	else {
		var member = members[list[item].member];
		var date = checkDate($('#graphPeriod'))[0];
		checkMemberStat(member, game, date, function () {
			item++;
			checkGraphMembers(list, game, item);
		});
	}
	;
}

function graphList(list) {
	$('#graphPeriod').attr('start', '2012-08-05');
	var table = new Array();
	var game = $('#graphMenuGame .button.active').attr('menu');
	var menu = $('#graphMenuParam .button.active').attr('menu');
	var date = checkDate($('#graphPeriod'));
	for (var item in list) {
		var row = new Object();
		var member = members[list[item].member];
		row.member = member.id;
		row.name = member.name.toLowerCase();
		var nameClass = 'name';
		if (memberID == member.id)
			nameClass += ' myself';
		row.smember = '<span class="' + nameClass + '" member="' + member.id + '">' + member.name + '</span>';
		row.regDate = member.regDate.toString();
		row.color = member.color;
		row.scolor = '<span class="graphColor" style="background-color:' + member.color + ';"></span>';
		var stat = member.getStat(game, date[1]);
		var oldStat = member.getStat(game, date[0]);
		if (oldStat == undefined)
			oldStat = member.getStat(game, member.games[game].load);
		if (stat != undefined && oldStat != undefined) {
			if (menu != 'active') {
				row.value = stat.ext[menu];
				row.diff = stat.ext[menu] - oldStat.ext[menu];
				row.svalue = '<span>' + toStr(stat.ext[menu], 2) + (row.diff == 0 ? '' : ' <c:diff,0>(' + toStr(row.diff, 2, true) + ')</c>') + '</span>';
			} else {
				row.value = stat.main.battles;
				row.diff = stat.main.battles - oldStat.main.battles;
				row.svalue = '<span>' + toStr(stat.main.battles, 2) + (row.diff == 0 ? '' : ' <c:diff,0>(' + toStr(row.diff, 2, true) + ')</c>') + '</span>';
			}
			;
			table.push(row);
		}
		;
	}
	;

	var tableHead = new Array();
	tableHead[0] = [['Цвет', '<span class="all">Все</span>'], 'scolor', 'regDate', 40, , 'center'];
	tableHead[1] = ['Игрок', 'smember', 'name', 140, , 'left'];
	tableHead[2] = [['Значение', '(изменение)'], 'svalue', ['value', 'diff'], 140, -1, 'center'];
	$('#graphList .table').table({
		header: tableHead,
		data: table,
		lineHeight: 40
	});
	for (var item in list) {
		if (!list[item].visible) {
			$('#graphList').find('.name[member="' + list[item].member + '"]').parent().parent().addClass('hide');
		}
		;
	}
	;
	var hide = false;
	$('#graphList .row').each(function () {
		if ($(this).hasClass('hide'))
			hide = true;
	});
	if (hide)
		$('#graphList .all').addClass('hide');

	wait($('#graphSheet'), false);

	$('#graphList .graphColor').click(function () {
		$(this).parent().parent().toggleClass('hide');
		if ($(this).parent().parent().hasClass('hide'))
			$('#graphList .all').addClass('hide');
		else {
			var hide = false;
			$('#graphList .row').each(function () {
				if ($(this).hasClass('hide'))
					hide = true;
			});
			if (!hide)
				$('#graphList .all').removeClass('hide');
		}
		;
		drawGraphics();
	});
	$('#graphList .all').click(function () {
		$(this).toggleClass('hide');
		if ($(this).hasClass('hide'))
			$('#graphList .row').addClass('hide');
		else
			$('#graphList .row').removeClass('hide');
		drawGraphics();
	});

	drawGraphics();
}

function drawGraphics() {
	$('#graphics').html('');
	var game = $('#graphMenuGame .button.active').attr('menu');
	var menu = $('#graphMenuParam .button.active').attr('menu');
	var date = checkDate($('#graphPeriod'));
	var min = 100000;
	var max = 1;

	var initDate = date[1].toString();
	var list = new Array();
	$('#graphList .table .row').each(function () {
		var member = $(this).find('.name').attr('member');
		if (!($(this).hasClass('hide')))
			list.push(member);
		if (members[member].games[game].init != undefined) {
			if (members[member].games[game].init < initDate) {
				if (members[member].games[game].init > date[0].toString())
					initDate = members[member].games[game].init;
				else
					initDate = date[0].toString();
			}
			;
		}
		;
	});

	if (initDate > date[0].toString()) {
		$('#graphPeriod').attr('start', initDate);
		drawGraphics();
	} else {
		var svgWidth = $('#graphics').width();
		var svgHeight = $('#graphics').height();
		var delta = 15;
		var maxCount = Math.ceil(svgWidth / delta);

		var dates = new Array();
		for (var curDate = new Date(date[0]); curDate.toInt() <= date[1].toInt(); curDate.setDate(curDate.getDate() + 1)) {
			dates.push(curDate.toString());
		}
		;
		var axis = new Array();
		var len = dates.length - 1;
		if (len > maxCount) {
			for (var i = 0; i < maxCount; i++) {
				var curPos = Math.round(len * i / (maxCount - 1));
				axis.push(dates[curPos]);
			}
			;
		} else
			axis = dates;
		len = axis.length - 1;

		var values = new Object();
		for (var item in list) {
			var member = members[list[item]];
			values[list[item]] = new Object();
			var battles = -1;
			for (var number in axis) {
				curDate = new Date(axis[number]);
				stat = member.getStat(game, curDate);
				if (stat != undefined) {
					value = stat.ext[menu];
					if (menu == 'active') {
						value = stat.main.battles;
						if (number == 0) {
							battles = value;
							value = 0;
						} else {
							value = value - battles;
							battles += value;
						}
						;
					}
					;
					values[list[item]][curDate.toString()] = value;
					if (min > value)
						min = value;
					if (max < value)
						max = value;
				}
				;
			}
			;
		}
		;

		var amplitude = max - min;
		var minStep = 0.1;
		if (menu == 'wins')
			minStep = 0.01;
		var step = amplitude / 30;
		var razr = Math.floor(step * 100).toString().length - 3;
		step = Math.max(minStep, Math.floor(step * Math.pow(10, -1 * razr)) / Math.pow(10, -1 * razr));
		max = Math.round((max + step) / step) * step;
		if (menu != 'active')
			min = Math.round((min - step) / step) * step;
		var steps = Math.round((max - min) / step);

		var svg = SVG.create('svg');
		var back = SVG.create('rect', {
			x: 0,
			y: 0,
			width: '100%',
			height: '100%',
			'stroke-width': '0px',
			stroke: '#545454',
			fill: '#222'
		});
		var rect = SVG.create('rect', {
			x: 0,
			y: 0,
			width: '100%',
			height: '100%',
			'stroke-width': '2px',
			stroke: '#545454',
			fill: 'none'
		});
		var xx = SVG.create('g', {
			stroke: '#2D2D2D'
		});
		for (var number in axis) {
			var x = 0.5 + Math.round(9900 * number / len) / 100;
			var line = SVG.create('line', {
				x1: x + '%',
				y1: '0%',
				x2: x + '%',
				y2: '100%',
				'stroke-width': '1px'
			});
			xx.append(line);
		}
		;
		var yy = SVG.create('g', {
			stroke: '#2D2D2D'
		});
		for (var i = 1; i < steps * 2; i++) {
			var y = Math.round(10000 * i / steps) / 100;
			line = SVG.create('line', {
				x1: '0%',
				y1: y + '%',
				x2: '100%',
				y2: y + '%',
				'stroke-width': '1px'
			});
			yy.append(line);
		}
		;
		svg.append(back);
		svg.append(xx);
		svg.append(yy);
		svg.append(rect);
		for (member in values) {
			var g1 = SVG.create('g', {
				class: 'member-line',
				stroke: members[member].color,
				member: member,
				'stroke-linecap': 'round'
			});
			var g2 = SVG.create('g', {
				class: 'member-point',
				stroke: members[member].color,
				member: member
			});
			var x1 = 0;
			var y1 = 0;
			for (var number in axis) {
				if (axis[number] in values[member]) {
					if (x1 == 0 && y1 == 0) {
						x1 = 0.5 + Math.round(9900 * number / len) / 100;
						y1 = Math.round(10000 * (1 - (values[member][axis[number]] - min) / (max - min))) / 100;
						var point = SVG.create('circle', {
							cx: x1 + '%',
							cy: y1 + '%',
							r: 3,
							member: member,
							date: axis[number],
							value: values[member][axis[number]]
						});
						if (menu != 'active')
							g2.append(point);
					} else {
						x2 = 0.5 + Math.round(9900 * number / len) / 100;
						y2 = Math.round(10000 * (1 - (values[member][axis[number]] - min) / (max - min))) / 100;
						line = SVG.create('line', {
							x1: x1 + '%',
							y1: y1 + '%',
							x2: x2 + '%',
							y2: y2 + '%'
						});
						point = SVG.create('circle', {
							cx: x2 + '%',
							cy: y2 + '%',
							r: 3,
							member: member,
							date: axis[number],
							value: values[member][axis[number]]
						});
						var preDate = '';
						if (number != 0) {
							preDate = new Date(axis[number - 1]);
							preDate.setDate(preDate.getDate() + 1);
							preDate = preDate.toString();
							if (preDate == axis[number])
								preDate = '';
							else if (number == 1)
								preDate = (new Date(axis[number - 1])).toString();
						}
						;
						var clr = hexToRgb(members[member].color);
						var rct = SVG.create('rect', {
							x: x1 + '%',
							y: y2 + '%',
							width: (x2 - x1) + '%',
							height: (100 - y2) + '%',
							fill: 'rgba(' + clr.r + ',' + clr.g + ',' + clr.b + ',0.5)',
							member: member,
							date: axis[number],
							predate: preDate,
							value: values[member][axis[number]],
							class: 'battles'
						});
						x1 = x2;
						y1 = y2;
						if (menu == 'active') {
							if (values[member][axis[number]] != 0)
								g2.append(rct);
						} else {
							g1.append(line);
							g2.append(point);
						}
						;
					}
					;
				}
				;
			}
			;
			svg.append(g1);
			svg.append(g2);
		}
		;

		$('#graphics').append(svg);
		$('#graphics g').hover(function () {
			var member = $(this).attr('member');
			$('#graphList .table').find('.name[member="' + member + '"]').parent().parent().addClass('current');
			if ($(this).attr('class') == 'member-line')
				$('#graphics g.member-point[member="' + member + '"]').attr('hover', 'true');
		}, function () {
			var member = $(this).attr('member');
			$('#graphList .table').find('.name[member="' + member + '"]').parent().parent().removeClass('current');
			$('#graphics g.member-point[member="' + member + '"]').attr('hover', 'false');
		});

		$('#graphList .table .row').hover(function () {
			var member = $(this).find('.name').attr('member');
			$(this).addClass('current');
			$('#graphics g[member="' + member + '"]').attr('hover', 'true');
		}, function () {
			var member = $(this).find('.name').attr('member');
			$(this).removeClass('current');
			$('#graphics g.member-line[member="' + member + '"]').attr('class', 'member-line');
			$('#graphics g[member="' + member + '"]').attr('hover', 'false');
		});

		$('#graphics g.member-point circle, #graphics g.member-point rect').each(function () {
			var member = $(this).attr('member');
			if (member != undefined) {
				var nameClass = 'name';
				if (memberID == members[member].id)
					nameClass += ' myself';
				var name = '<span class="' + nameClass + '">' + members[member].name + '</span>';
				var date = (new Date($(this).attr('date'))).toString('long');
				var preDate = $(this).attr('predate');
				if (preDate != undefined && preDate != '')
					date = diffDates(preDate, $(this).attr('date'));
				var value = '<span>' + toStr($(this).attr('value'), 2) + '</span>';
				if ($(this).attr('class') == 'battles')
					value = '<span>' + toStr($(this).attr('value')) + ' ' + getWordEnding($(this).attr('value'))[3] + '</span>';
				var s = name + '<br />' + '<span>' + date + '</span>: ' + value;
				$(this).attr('title', '').tooltip({
					content: s,
					position: {
						my: 'right-10 top',
						at: 'left top'
					},
					hide: {
						duration: 0
					},
					tooltipClass: 'tooltip'
				});
			}
			;
		});
	}
	;
}

function diffDates(firstDate, secondDate) {
	var ret = '';

	var date1 = new Date(firstDate);
	var date2 = new Date(secondDate);
	if (date1.getFullYear() != date2.getFullYear()) {
		ret = date1.toString() + ' - ' + date2.toString('full');
	} else if (date1.getMonth() != date2.getMonth()) {
		ret = date1.toString('full').slice(0, -5) + ' - ' + date2.toString('full');
	} else if (date1.getDate() != date2.getDate()) {
		ret = date1.getDate().toString() + ' - ' + date2.toString('full');
	} else
		ret = date2.toString('full');

	return ret;
}

function statSheet() {
	sheets.stat = viewMembers;
	$('#statMenuGame .button').removeClass('active');
	$('#statMenuGame .button:first-child').addClass('active');
	buttonStatGame();
}

function buttonStatGame() {
	var game = $('#statMenuGame .button.active').attr('menu');
	$('#statMenu .button').removeClass('active');
	$('#statMenu .button:first-child').addClass('active');
	var list = new Array();
	for (member in members) {
		if (members[member].clan == clanData.id || viewMembers == 'all') {
			if (game in members[member].games && members[member].games[game] != undefined && members[member].games[game] != null) {
				list.push(member);
				if (members[member].games[game].load != undefined) {
					var stat = members[member].getStat(game);
					if (stat != undefined && stat.main.battles == 0)
						delete list[member];
				}
				;
			}
			;
		}
		;
	}
	;
	wait($('#statSheet'));
	checkStatMembers(list, game, 0);
}

function checkStatMembers(list, game, item) {
	if (item >= list.length)
		buttonStat();
	else {
		var member = members[list[item]];
		var date = new Date();
		checkMemberStat(member, game, date, function () {
			item++;
			checkStatMembers(list, game, item);
		});
	}
	;
}

function buttonStat() {
	wait($('#statSheet'), false);
	$('#statTables>div').hide();
	var menu = $('#statMenu .button.active').attr('menu');
	if (menu == 'all')
		statAll();
	if (menu == 'eff')
		statEff();
	if (menu == 'medals')
		statMedals();
	if (menu == 'technics')
		statTechnics();
}

function statAll() {
	$('#statAll .table').html('');
	$('#statAll').css({
		display: 'block'
	});
	var game = $('#statMenuGame .button.active').attr('menu');
	var table = new Array();
	for (var item in members) {
		var member = members[item];
		if (member.clan == clanData.id || viewMembers == 'all') {
			var stat = member.getStat(game);
			if (stat != undefined) {
				var row = new Object();
				row.name = member.name;
				var nameClass = 'name' + (memberID == member.id ? ' myself' : '');
				row.sname = '<span class="' + nameClass + '" member="' + member.id + '">' + row.name + '</span>';
				row.battles = stat.main.battles;
				row.sbattles = '<c:battles,n>' + stat.main.battles + '</c>';
				row.wins = stat.ext.wins;
				row.swins = '<span style="color:' + effColor(game, row.wins, 'WR') + ';">' + toStr(row.wins, 2) + '%</span>';
				row.survived = stat.ext.survived;
				row.ssurvived = '<c:survived,m>' + toStr(row.survived, 2) + '%</c>';
				row.technics = stat.ext.technics;
				row.stechnics = '<c:technics,m>' + toStr(row.technics, 2) + '</c>';
				if (game == 'wot') {
					row.effBS = stat.ext.effBS;
					row.seffBS = '<span style="color:' + effColor(game, row.effBS, 'BS') + ';">' + toStr(row.effBS, 2) + '</span>';
					row.effWN = stat.ext.effWN;
					row.seffWN = '<span style="color:' + effColor(game, row.effWN, 'WN') + ';">' + toStr(row.effWN, 2) + '</span>';
					row.effWN6 = stat.ext.effWN6;
					row.seffWN6 = '<span style="color:' + effColor(game, row.effWN6, 'WN6') + ';">' + toStr(row.effWN6, 2) + '</span>';
					row.effWN8 = stat.ext.effWN8;
					row.seffWN8 = '<span style="color:' + effColor(game, row.effWN8, 'WN8') + ';">' + toStr(row.effWN8, 2) + '</span>';
				}
				;

				table.push(row);
			}
			;
		}
		;
	}
	;
	var header = new Array();
	header[0] = ['Имя', 'sname', 'name', 140, 1, 'left'];
	header[1] = ['Проведено боёв', 'sbattles', 'battles', 100, -1, 'center'];
	header[2] = ['Побед', 'swins', 'wins', 100, -1, 'center'];
	header[3] = ['Выжил', 'ssurvived', 'survived', 100, -1, 'center'];
	if (game == 'wot') {
		header[4] = ['Рейтинг<br />BS', 'seffBS', 'effBS', 100, -1, 'center'];
		header[5] = ['Рейтинг<br />WN', 'seffWN', 'effWN', 100, -1, 'center'];
		header[6] = ['Рейтинг<br />WN6', 'seffWN6', 'effWN6', 100, -1, 'center'];
		header[7] = ['Рейтинг<br />WN8', 'seffWN8', 'effWN8', 100, -1, 'center'];
		header[8] = ['Уровень техники', 'stechnics', 'technics', 80, -1, 'center'];
	} else
		header[4] = ['Уровень техники', 'stechnics', 'technics', 80, -1, 'center'];
	$('#statAll .table').table({
		header: header,
		data: table,
		lineHeight: 40
	});
}

function statEff() {
	$('#statEff .table').html('');
	$('#statEff').css({
		display: 'block'
	});
	var game = $('#statMenuGame .button.active').attr('menu');
	var table = new Array();
	for (var item in members) {
		var member = members[item];
		if (member.clan == clanData.id || viewMembers == 'all') {
			var stat = member.getStat(game);
			if (stat != undefined) {
				var row = new Object();
				row.name = member.name;
				var nameClass = 'name' + (memberID == member.id ? ' myself' : '');
				row.sname = '<span class="' + nameClass + '" member="' + member.id + '">' + row.name + '</span>';
				row.battles = stat.main.battles;
				row.sbattles = '<c:battles,n>' + stat.main.battles + '</c>';
				row.frags = stat.ext.frags;
				row.sfrags = '<c:frags,m>' + toStr(row.frags, 2) + '</c>';
				row.damage = stat.ext.damage;
				row.sdamage = '<c:damage,m>' + toStr(row.damage, 2) + '</c>';
				row.xp = stat.ext.xp;
				row.sxp = '<c:xp,m>' + toStr(row.xp, 2) + '</c>';
				if (game == 'wot' || game == 'wotb') {
					row.spotted = stat.ext.spotted;
					row.sspotted = '<c:spotted,m>' + toStr(row.spotted, 2) + '</c>';
				}
				;
				if (game == 'wot' || game == 'wotb' || game == 'wows') {
					row.capture = stat.ext.capture;
					row.scapture = '<c:capture,m>' + toStr(row.capture, 2) + '</c>';
					row.dropped = stat.ext.dropped;
					row.sdropped = '<c:dropped,m>' + toStr(row.dropped, 2) + '</c>';
				}
				;
				if (game == 'wot' || game == 'wotb' || game == 'wowp') {
					row.hits = stat.ext.hits;
					row.shits = '<c:hits,m>' + toStr(row.hits, 2) + '</c>';
				}
				;

				table.push(row);
			}
			;
		}
		;
	}
	;
	var header = new Array();
	header[0] = ['Имя', 'sname', 'name', 140, 1, 'left'];
	header[1] = ['Проведено боёв', 'sbattles', 'battles', 100, -1, 'center'];
	header[2] = ['Уничтожено', 'sfrags', 'frags', 100, -1, 'center'];
	header[3] = ['Повреждения', 'sdamage', 'damage', 100, -1, 'center'];
	header[4] = ['Опыт', 'sxp', 'xp', 100, -1, 'center'];
	if (game == 'wot' || game == 'wotb') {
		header[5] = ['Обнаружено', 'sspotted', 'spotted', 100, -1, 'center'];
		header[6] = ['Захват', 'scapture', 'capture', 100, -1, 'center'];
		header[7] = ['Защита', 'sdropped', 'dropped', 100, -1, 'center'];
		header[8] = ['% попадания', 'shits', 'hits', 80, -1, 'center'];
	}
	;
	if (game == 'wowp') {
		header[5] = ['% попадания', 'shits', 'hits', 80, -1, 'center'];
	}
	;
	if (game == 'wows') {
		header[5] = ['Захват', 'scapture', 'capture', 100, -1, 'center'];
		header[6] = ['Защита', 'sdropped', 'dropped', 100, -1, 'center'];
	}
	;
	$('#statEff .table').table({
		header: header,
		data: table,
		lineHeight: 40
	});
}

function statMedals() {
}

function statTechnics() {
	$('#statTechnics').css({
		display: 'block'
	});
	$('.statTechnics').hide();
	var menu = $('#statTechnicsMenu .button.active').attr('menu');
	if (menu == 'levels')
		statTechnicsLevels();
	if (menu == 'tree')
		statTechnicsTree();
}

function statTechnicsLevels() {
	$('#statTechnicsTable').show();
	var game = $('#statMenuGame .button.active').attr('menu');
	var table = [];
	for (var item in members) {
		var member = members[item];
		if (member.clan == clanData.id || viewMembers == 'all') {
			var stat = member.getStat(game);
			if (stat != undefined) {
				var row = new Object();
				row.name = member.name;
				var nameClass = 'name' + (memberID == member.id ? ' myself' : '');
				row.sname = '<span class="' + nameClass + '" member="' + member.id + '">' + row.name + '</span>';
				for (var level = 1; level <= 10; level++)
					row['level' + level] = 0;
				row.all = 0;
				for (var tech in stat.technics) {
					if (tech in games[game].technics) {
						level = games[game].technics[tech].level;
						row['level' + level]++;
						row['slevel' + level] = '<span class="level" level="' + level + '" member="' + member.id + '"><c:level' + level + ',m>' + row['level' + level] + '</c></span>';
						row.all++;
					}
					;
				}
				;
				row.sall = '<span class="level" level="all"><c:all,m>' + row.all + '</c></span>';

				table.push(row);
			}
			;
		}
		;
	}
	var header = [];
	header[0] = ['Имя', 'sname', 'name', 140, 1, 'left'];
	for (level = 1; level <= 10; level++) {
		header[level] = [level, 'slevel' + level, 'level' + level, 50, -1, 'center'];
	}
	header[11] = ['Всего', 'sall', 'all', 70, -1, 'center'];
	$('#statTechnicsTable .table').table({
		header: header,
		data: table,
		lineHeight: 40
	});
	resize();

	$('#statTechnicsTable .level').each(function () {
		var level = $(this).attr('level');
		if (level != 'all') {
			var game = $('#statMenuGame .button.active').attr('menu');
			var member = members[$(this).attr('member')];
			var list = [];
			var stat = member.getStat(game);
			if (stat != undefined) {
				for (var tech in stat.technics) {
					if (tech in games[game].technics && games[game].technics[tech].level == level) {
						var item = {};
						item.nation = games[game].technics[tech].nation;
						item.sortNation = clanData.sortNations.indexOf(item.nation);
						item.type = clanData.sortTypes.indexOf(games[game].technics[tech].type);
						item.image = games[game].technics[tech].image;
						item.prem = games[game].technics[tech].isPrem;
						var style = '';
						item.name = games[game].technics[tech].name;
						if (game == 'wot') {
							item.name = games[game].technics[tech].shortName;
							style = "background-position: -5px center;";
						}
						if (game == 'wotb')
							style = "background-position: -40px center; background-size: 150px;";
						if (game == 'wowp')
							item.name = games[game].technics[tech].nameRu;
						if (game == 'wows')
							style = "background-position: left center; background-size: 80px;";

						item.technic = '<div class="item-technic"><div class="nation"><img src="images/nations/' + item.nation + '.png" /><span><span>' + romanNum[level] + '</span></span></div><div class="technic" style="background-image: url(\'' + item.image + '\'); ' + style + '"><span class="' + (item.prem == 1 ? 'prem' : '') + '">' + item.name + '</span></div></div>';

						list.push(item);
					}
				}
			}
			list.sort(function (a, b) {
				var res = 0;
				if (a['name'] > b['name'])
					res = 1;
				if (a['name'] < b['name'])
					res = -1;
				if (a['type'] > b['type'])
					res = 1;
				if (a['type'] < b['type'])
					res = -1;
				if (a['prem'] > b['prem'])
					res = 1;
				if (a['prem'] < b['prem'])
					res = -1;
				if (a['sortNation'] > b['sortNation'])
					res = 1;
				if (a['sortNation'] < b['sortNation'])
					res = -1;
				return res;
			});

			var s = '';
			for (item in list)
				s += list[item].technic;
			var tlClass = 'tooltip';
			if (list.length * 35 > $(window).height())
				tlClass = 'tooltip two-columns';
			$(this).parent().attr('title', '').tooltip({
				content: s,
				position: {
					my: "left center",
					at: "right center"
				},
				hide: {
					duration: 0
				},
				tooltipClass: tlClass
			});
		}
	});
}

function statTechnicsTree() {
}

function checkDate(elem) {
	var lastDay = new Date();
	var date = new Date();
	var type = $(elem).attr('type');
	var start = $(elem).attr('start');
	if (start == 'month') {
		var firstDay = new Date();
		firstDay.setMonth(firstDay.getMonth() - 1);
	} else if (start == 'year') {
		firstDay = new Date();
		firstDay.setYear(firstDay.getFullYear() - 1);
	} else
		firstDay = new Date(start);
	if (type == 'day') {
		var val = $(elem).find('.inputDate[name="date"]').val();
		var curDate = val == '' ? new Date() : val.toDate();
		date = new Date();
		if (curDate !== undefined)
			date = new Date(curDate);
		if (curDate > lastDay)
			date = new Date(lastDay);
		if (curDate < firstDay)
			date = new Date(firstDay);
		$(elem).find('.inputDate[name="date"]').val(date.toString('long'));
		return date;
	}
	if (type == 'period') {
		var val1 = $(elem).find('.inputDate[name="date1"]').val();
		var val2 = $(elem).find('.inputDate[name="date2"]').val();
		var date1 = new Date();
		var date2 = new Date();
		if ($(elem).attr('id') == 'graphPeriod')
			date1.setMonth(date1.getMonth() - 1);
		if (val1 != undefined && val1 != '')
			date1 = val1.toDate();
		if (val2 != undefined && val2 != '')
			date2 = val2.toDate();
		if (date2 > lastDay)
			date2 = new Date(lastDay);
		if (date1 >= date2) {
			date1 = new Date(date2);
			date1.setDate(date2.getDate() - 1);
		}
		;
		if (date1 < firstDay)
			date1 = new Date(firstDay);
		if (date2 <= date1) {
			date2 = new Date(date1);
			date2.setDate(date1.getDate() + 1);
		}
		;
		$(elem).find('.inputDate[name="date1"]').val(date1.toString('long'));
		$(elem).find('.inputDate[name="date2"]').val(date2.toString('long'));
		$(elem).find('button').hide();
		var diff = date2.toInt() - date1.toInt();
		if (diff == 1) {
			$(elem).find('button').show();
			$(elem).find('button').attr({
				period: 'day'
			});
			$(elem).find('button .ui-button-text').text('День');
		}
		;
		if (diff == 7) {
			$(elem).find('button').show();
			$(elem).find('button').attr({
				period: 'week'
			});
			$(elem).find('button .ui-button-text').text('Неделя');
		}
		;
		if (date1.getDate() == date2.getDate()) {
			var numMonth1 = date1.getYear() * 12 + date1.getMonth();
			var numMonth2 = date2.getYear() * 12 + date2.getMonth();
			diff = numMonth2 - numMonth1;
			if (diff == 1) {
				$(elem).find('button').show();
				$(elem).find('button').attr({
					period: 'month'
				});
				$(elem).find('button .ui-button-text').text('Месяц');
			}
			;
			if (diff == 12) {
				$(elem).find('button').show();
				$(elem).find('button').attr({
					period: 'year'
				});
				$(elem).find('button .ui-button-text').text('Год');
			}
			;
		}
		;
		date = new Array();
		date = [date1, date2];
		return date;
	}
	return null;
}

function opacityOfDate(date) {
	var opacity = 0.2;
	var checkDays = 10;
	if (date != null) {
		var period = date.toDate().wait();
		opacity = 0.2 + 0.8 * checkDays / (period + checkDays);
	}
	;
	return opacity;
}

function query(query, func) {
	if (query == 'main') {
		wait($('#mainSheet'));
		myQuery('ajax/getMainData.php', {},
			function (answer) {
				wait($('#mainSheet'), false);
				sheets['main'] = true;
				clanData = answer.data.data;
				roles = answer.data.roles;
				clans = answer.data.clans;

				games.wot = answer.data.wot;
				games.wotb = answer.data.wotb;
				games.wowp = answer.data.wowp;
				games.wows = answer.data.wows;
				myQuery('ajax/getMembers.php', {},
					function (answer) {
						for (var member in answer.data)
							members[answer.data[member]['id']] = new Member(answer.data[member]);
						mainSheet();
					});
			});
	}
	if (query == 'admin') {
		wait($('#adminSheet'));
		myQuery('ajax/getAdminData.php', {
			'rights': rights.str
		}, function (answer) {
			wait($('#adminSheet'), false);
			sheets['admin'] = true;

			if ('wot' in answer.data && 'technicChanges' in answer.data.wot)
				games.wot.technicChanges = answer.data.wot.technicChanges;
			if ('wot' in answer.data && 'medalChanges' in answer.data.wot)
				games.wot.medalChanges = answer.data.wot.medalChanges;
			if ('wotb' in answer.data && 'technicChanges' in answer.data.wotb)
				games.wotb.technicChanges = answer.data.wotb.technicChanges;
			if ('wotb' in answer.data && 'medalChanges' in answer.data.wotb)
				games.wotb.medalChanges = answer.data.wotb.medalChanges;
			if ('wowp' in answer.data && 'technicChanges' in answer.data.wowp)
				games.wowp.technicChanges = answer.data.wowp.technicChanges;
			if ('wowp' in answer.data && 'medalChanges' in answer.data.wowp)
				games.wowp.medalChanges = answer.data.wowp.medalChanges;
			if ('wows' in answer.data && 'technicChanges' in answer.data.wows)
				games.wows.technicChanges = answer.data.wows.technicChanges;
			if ('wows' in answer.data && 'medalChanges' in answer.data.wows)
				games.wows.medalChanges = answer.data.wows.medalChanges;

			visitors = answer.data.visitors;

			adminSheet();
		});
	}
}

function myQuery(address, options, func, prefunc) {
	$.ajax({
		url: address,
		type: 'post',
		dataType: 'json',
		data: options,
		success: function (answer) {
			if (prefunc != undefined)
				prefunc();
			if (answer.status == 'ok') {
				if (func != undefined)
					func(answer);
			} else {
				showMessage(answer.error, 'error');
			}
		},
		error: function (error) {
			if (prefunc != undefined)
				prefunc();
			showMessage(error, 'error');
		}
	});
}

function showMessage(message, type, func) {
	$('#waitAnimation').hide();
	if (message == '') {
		$('#messageButtons').find('button').unbind('click');
		$('#messageText').html('&nbsp;');
		$('#messageButtons').hide();
		$('#messageBlock').removeClass('error');
		$('#message').hide();
	} else {
		if (typeof(type) == 'string' && type == 'error') {
			$('#messageBlock').addClass('error');
			if (typeof(message) == 'string')
				$('#messageText').html('Ошибка<br />' + message);
			else {
				if (message == undefined)
					var msg = 'Ошибка';
				else {
					msg = 'Ошибка загрузки';
					if ('status' in message && message.status == 404)
						msg += '<br />Файл отсутствует';
					else
						msg += '<br />' + ('responseText' in message && typeof(message.responseText) == 'string') ? parseError(message.responseText) : '';
				}
				$('#messageText').html(msg);
			}
		} else
			$('#messageText').html(message);
		if (typeof(type) == 'string' && type == 'question') {
			$('#messageButtons').show();
			$('#messageButtons button').click(function () {
				var button = $(this).parent().attr('id');
				if (button == 'messageButtonOk') {
					$('#question').hide();
					if (func != undefined)
						func();
				} else if (button == 'messageButtonCancel')
					showMessage('');
			});
		}
		$('#message').show();
	}
	resize();
}

function parseError(message) {
	var pos = message.indexOf('{');
	var mess = message.slice(0, pos);
	var json = JSON.parse(message.slice(pos));
	mess += '<br />' + json['error'];
	return mess;
}

function effColor(game, value, eff) {
	eff = eff.toLowerCase();
	var color = '';
	if (value == 0)
		color = '#FE0E00';
	if (value != 0 && value != null) {
		if (typeof(value) != 'string') {
			if (eff == 'wr') {
				for (var val in games.wot.data.effectColor.wr) {
					if (value >= games.wot.data.effectColor.wr[val].value)
						color = games.wot.data.effectColor.wr[val].color;
				}
			} else {
				for (val in games[game].data.effectColor[eff]) {
					if (value >= games[game].data.effectColor[eff][val].value)
						color = games[game].data.effectColor[eff][val].color;
				}
			}
		} else if (eff == 'bs')
			color = colorEffects['bs'][value]['color'];
	}
	return color;
}

function findCrit(data, field, np) {
	var res = 0;
	if (np == 'min')
		res = 1000;
	for (var i in data) {
		if (np == 'max' && data[i][field] > res)
			res = data[i][field];
		if (np == 'min' && data[i][field] < res)
			res = data[i][field];
	}
	return res;
}

function colorDiff(min, mid, max, cur) {
	if (mid == 'm')
		mid = max - ((max - min) / 2);
	if (mid == 'n')
		mid = min;
	var c_max = [0, 255, 33]; //зелёный
	var c_mid = [255, 216, 0]; //жёлтый
	var c_min = [255, 50, 50]; //красный
	if ((cur > mid && max > min) || (cur < mid && max < min)) {
		var percent = Math.abs(cur - mid) / Math.abs(max - mid);
		var color = addColor(c_mid, c_max, percent);
	} else {
		if (min == mid)
			percent = 0;
		else
			percent = Math.abs(cur - mid) / Math.abs(min - mid);
		color = addColor(c_mid, c_min, percent);
	}
	return color;
}

function addColor(color1, color2, percent) {
	var color = [0, 0, 0];
	for (var i in color1) {
		color[i] = (color1[i] + Math.round((color2[i] - color1[i]) * percent)).oldToString(16);
		if (color[i].length == 1)
			color[i] = '0' + color[i];
	}
	return '#' + color[0] + color[1] + color[2];
}

function replace_string(txt, cut_str, paste_str) {
	var ht = '' + txt;
	var find = ht.indexOf(cut_str);
	while (find != -1) {
		find = ht.indexOf(cut_str);
		if (find >= 0)
			ht = ht.substr(0, find) + paste_str + ht.substr(find + cut_str.length);
	}
	return ht
}

function copyArray(obj) {
	var data = [];
	for (var key in obj) {
		if (typeof(obj[key]) == 'object')
			data[key] = copyArray(obj[key]);
		else
			data[key] = obj[key];
	}
	return data;
}

function getWordEnding(number) {
	var firstDigit = number - Math.round(number / 10) * 10;
	var secondDigit = (number - Math.round(number / 100) * 100 - firstDigit) / 10;
	var ending = ['лет', 'месяцев', 'дней', 'боёв', 'посещений'];
	if (secondDigit != 1) {
		if (firstDigit == 1)
			ending = ['год', 'месяц', 'день', 'бой', 'посещение'];
		if (firstDigit == 2 || firstDigit == 3 || firstDigit == 4)
			ending = ['года', 'месяца', 'дня', 'боя', 'посещения'];
	}
	return ending;
}

function toInt(str) {
	var ret = parseInt(str);
	if (str == null || str == undefined)
		ret = 0;
	return ret;
}

function toStr(num, nums, sign) {
	if (nums == '')
		var ret = num.oldToString(10).replace('.', ',');
	else {
		var nm = nums || 0;
		ret = (Math.round(Math.pow(10, nm) * num) / Math.pow(10, nm)).oldToString(10).replace('.', ',');
	}
	if (num > 0 && sign != undefined && sign == true)
		ret = '+' + ret;
	return ret;
}

function imgClassTank(techClass) {
	var ret = '';
	if (techClass == 1)
		ret = '//static-ptl-ru.gcdn.co/static/4.2.6/common/img/classes/class-3.png';
	if (techClass == 2)
		ret = '//static-ptl-ru.gcdn.co/static/4.2.6/common/img/classes/class-2.png';
	if (techClass == 3)
		ret = '//static-ptl-ru.gcdn.co/static/4.2.6/common/img/classes/class-1.png';
	if (techClass == 4)
		ret = '//static-ptl-ru.gcdn.co/static/4.2.6/common/img/classes/class-ace.png';
	return ret;
}

function addZero(n) {

	n = n.toString();
	return n.length > 1 ? n : (+n > 0) ? "0" + n : n;
}

function hexToRgb(hex) {
	var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	return result ? {
		r: parseInt(result[1], 16),
		g: parseInt(result[2], 16),
		b: parseInt(result[3], 16)
	}
		: null;
}
