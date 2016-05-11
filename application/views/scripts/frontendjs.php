/* jshint -W100 */
/* jshint undef: true, unused: true */
/* globals ymaps, confirm, style_src, usermap, style_paths, yandex_styles, yandex_markers, style_circles, style_polygons, styleAddToStorage */
function init() {
	"use strict";
	// Encoding: UTF-8
	var a,
		cursor,
		map,
		layerTypes,
		foreground,
		background,
		found = {},
		tileServerID = parseInt(Math.random() * (4-1) + 1).toString(),
		tileServerLit= { "0": "a","1": "b","2": "c","3": "d","4": "e","5": "f" },
		showMode = 'direct',
		maptypes = {
			1: 'yandex#map',
			2: 'yandex#satellite',
			3: 'yandex#hybrid',
			4: 'yandex#publicMap',
			5: 'yandex#publicMapHybrid'
		},
		typeicons = {
			1: 'http://api.korzhevdp.com/images/marker.png',
			2: 'http://api.korzhevdp.com/images/layer-shape-polyline.png',
			3: 'http://api.korzhevdp.com/images/layer-shape-polygon.png',
			4: 'http://api.korzhevdp.com/images/layer-shape-ellipse.png',
			5: 'http://api.korzhevdp.com/images/layer-select.png'
		},
		searchControl = new ymaps.control.SearchControl({ provider: 'yandex#publicMap', boundedBy: [[40, 65], [42, 64]], strictBounds: 1 }),
		// Создаем шаблон для отображения контента балуна
		genericBalloon = ymaps.templateLayoutFactory.createClass(
			'<div class="ymaps_balloon">' +
				'<div class="well" id="l_photo" data-toggle="modal" data-target="#modal_pics" loc=$[properties.ttl|0] style="float:left;margin:3px;cursor:pointer;padding:2px;background-color:#DDDDDD;">' +
					'<img src="[if properties.img]/uploads/mid/$[properties.ttl]/$[properties.img][else]http://api.korzhevdp.com/images/nophoto.gif[endif]" alt="мини" id="sm_src_pic">' +
				'</div>' +
				'<b>$[properties.type|тип не указан]:</b> $[properties.name|без имени]<br>' +
				'<b>' + mp.headers[0] + ':</b> $[properties.description|не указан]<br>' +
				'<b>' + mp.headers[1] + ':</b> $[properties.contact|контактное лицо]<br><br>' +
				'<a href="$[properties.link|ссылка]" style="margin-bottom:10px;">' + mp.headers[2] + '</a>' +
				'</div>'
		),
		paidBalloon = ymaps.templateLayoutFactory.createClass(
			'<div class="ymaps_big_balloon">' +
				'<div id="l_photo" data-toggle="modal" data-target="#modal_pics" loc=$[properties.ttl|0]>' +
					'<img src="[if properties.img]/uploads/mid/$[properties.ttl]/$[properties.img][else]http://api.korzhevdp.com/images/nophoto.gif[endif]" alt="мини" id="sm_src_pic">' +
				'</div>' +
				'<span class="tlabel">$[properties.type|тип не указан]</span> <span class="name">$[properties.name|без имени]</span><br>' +
				'<span class="tlabel">' + mp.headers[0] + ':</span> <span class="addr">$[properties.description|не указан]</span><br>' +
				'<span class="tlabel">' + mp.headers[1] + ':</span> <span class="cont">$[properties.contact|контактное лицо]<span><br><br>' +
				'<a class="btn btn-link" href="$[properties.link|ссылка]" style="margin-bottom:10px;">' + mp.headers[2] + '</a>' +
				'</div>'
		),
		a_objects    = new ymaps.GeoObjectCollection(),
		b_objects    = new ymaps.GeoObjectCollection(),
		c_objects    = new ymaps.GeoObjectCollection(),
		paid_objects = new ymaps.GeoObjectCollection();

	map = new ymaps.Map("YMapsID", {
		center:    mp.center,
		zoom:      mp.zoom,
		type:      maptypes[mp.type],
		behaviors: ['default', 'scrollZoom']
	}, {
		projection: ymaps.projection.sphericalMercator,
		/*
		autoFitToViewport: "always",
		restrictMapArea: true
		*/
		suppressMapOpenBlock : true,
		yandexMapAutoSwitch  : false
	});

	layerTypes = {
		1: {
			func  : function () {return new ymaps.Layer(function (tile, zoom) {return "http://mt" + tileServerID + ".google.com/vt/lyrs=m&hl=" + mp.lang + "&x=" + tile[0] + "&y=" + tile[1] + "&z=" + zoom + "&s=Galileo";}, {tileTransparent: 1, zIndex:1000});},
			folder: "",
			label : "google#map",
			name  : "Гуглотест",
			layers: ["google#map"]
		}
	}
	for (a in layerTypes) {
		if (layerTypes.hasOwnProperty(a)) {
			ymaps.layer.storage.add(layerTypes[a].label, layerTypes[a].func);
			ymaps.mapType.storage.add(layerTypes[a].label, new ymaps.MapType(layerTypes[a].name, layerTypes[a].layers));
		}
	}

	map.geoObjects.add(a_objects);
	map.geoObjects.add(b_objects);
	map.geoObjects.add(c_objects);
	map.geoObjects.add(paid_objects);

	cursor = map.cursors.push('crosshair', 'arrow');
	cursor.setKey('arrow');
	map.controls.add('mapTools').add(searchControl);
	ymaps.layout.storage.add('generic#balloonLayout', genericBalloon);
	ymaps.layout.storage.add('paid#balloonLayout', paidBalloon);

	//назначаем опции оверлеев в коллекции (в данном случае - балун)
	a_objects.options.set({
		balloonContentBodyLayout: 'generic#balloonLayout',
		balloonMaxWidth: 400,// Максимальная ширина балуна в пикселах
		balloonMinWidth: 400,
		hasHint: 1,
		hasBalloon: 1,
		draggable: 0
	});

	paid_objects.options.set({
		balloonContentBodyLayout: 'paid#balloonLayout',
		balloonMaxWidth: 400,// Максимальная ширина балуна в пикселах
		balloonMinWidth: 400,
		hasHint: 1,
		hasBalloon: 1,
		draggable: 0
	});

	b_objects.options.set({
		balloonContentBodyLayout: 'generic#balloonLayout',
		balloonMaxWidth: 400,
		balloonMinWidth: 400,
		hasHint: 1,
		hasBalloon: 1,
		draggable: 0
	});

	function load_mapset(mapset) {

		function place_objectsWF(source, layer, found) {
			var b,
				ttl,
				object;
			$("#resultBody").empty();
			for (b in source) {
				if (source.hasOwnProperty(b)) {
					ttl = parseInt(b, 10);
					if (found[ttl] !== undefined) {
						insertObject(source, ttl, layer);
					}
				}
			}
		}

		function place_objects(source, layer) {
			var b,
				ttl,
				object;
			$("#resultBody").empty();
			for (b in source) {
				if (source.hasOwnProperty(b)) {
					ttl = parseInt(b, 10);
					insertObject(source, ttl, layer);
				}
			}
		}

		function makeObject(src, id) {
			var b,
				geometry,
				options,
				properties,
				object,
				src,
				newattr,
				count = 0;
			options      = ymaps.option.presetStorage.get(src.attr); //назначаем опции из существующих пресетов или из созданных нами вручную
			if (src.attr.split('#')[0] === 'default') {
				newattr  = [ 'twirl', src.attr.split('#')[1] ].join('#');
				options  = ymaps.option.presetStorage.get(newattr);
				src.attr = newattr;
			}
			properties = {		// свойства  у всех фигур одинаковые - семантика из базы данных и предвычисляемые службные поля
				attr		: src.attr,
				type		: src.type,
				contact		: src.contact,
				description	: src.description,
				hintContent	: src.name,
				img			: src.img,
				link		: src.link,
				name		: src.name,
				pr			: src.pr,
				paid		: src.p,
				ttl			: id
			};
			//console.log(c);
			if (src.pr === 1) { // точка
				geometry = src.coord.split(","); // создаём объект геометрии (или, если достаточно по условиям, - массив)
				object   = new ymaps.Placemark(geometry, properties, options); // генерируем оверлей
				object.options.set('zIndex', 100);
			}
			if (src.pr === 2) { //ломаная
				geometry = new ymaps.geometry.LineString.fromEncodedCoordinates(src.coord);
				object   = new ymaps.Polyline(geometry, properties, options);
				object.options.set('zIndex', 110);
			}
			if (src.pr === 3) { // полигон
				geometry = new ymaps.geometry.Polygon.fromEncodedCoordinates(src.coord);
				object   = new ymaps.Polygon(geometry, properties, options);
				object.options.set('zIndex', 10);
			}
			if (src.pr === 4) { // круг
				geometry = new ymaps.geometry.Circle([parseFloat(src.coord.split(",")[0]), parseFloat(src.coord.split(",")[1])], parseFloat(src.coord.split(",")[2]));
				object   = new ymaps.Circle(geometry, properties, options);
				object.options.set('zIndex', 20);
			}
			if (src.pr === 5) { // прямоугольник
				geometry = new ymaps.geometry.Rectangle([
					[parseFloat(src.coord.split(",")[0]), parseFloat(src.coord.split(",")[1])],
					[parseFloat(src.coord.split(",")[2]), parseFloat(src.coord.split(",")[3])]
				]);
				object   = new ymaps.Rectangle(geometry, properties, options);
				object.options.set('zIndex', 30);
			}
			return object;
			//object.options.set('visible', ((showMode === 'direct') ? 0 : 1));
		}

		function insertObject(source, ttl, layer) {
			var object = makeObject(source[ttl], ttl),
				comp   = source[ttl].comp,
				a = 0;
			function place_children() {
				if (a === comp.length || cdata === undefined || cdata[comp[a]] === undefined) {
					return false
				}
				insertObject(cdata, comp[a], 'c');
				a++;
				setTimeout(place_children, 100);
			}
			if (layer === 'a') {
				if (source[ttl].paid === 1) {
					paid_objects.add(object);
				} else {
					a_objects.add(object);
				}
				if(comp.length) {
					place_children();
				}
			}
			if (layer === 'b') {
				b_objects.add(object);
			}
			if (layer === 'c') {
				c_objects.add(object);
			}
			$("#resultBody").append("<li ref=" + object.properties.get('ttl') + "><img src=" + typeicons[object.properties.get("pr")] + ">" + object.properties.get('name') + "</li>");
		}

		function unfilter_collections() {
			$("#resultBody").empty();
			a_objects.removeAll();
		}

		function add_search() {
			$("#resultBody li").unbind().click(function () {
				$("#resultBody li").removeClass("active");
				$(this).addClass("active");
				select_object(parseInt($(this).attr('ref'), 10));
			});
		}

		function select_object(id) {
			var functions = {
					'Point'			: function (item) {
						item.balloon.open(item.geometry.getCoordinates());
						map.setCenter(item.geometry.getCoordinates());
					},
					'LineString'	: function (item) {
						item.options.set(ymaps.option.presetStorage.get('routes#current'));
						item.balloon.open(item.geometry.getCoordinates()[0]);
						map.setBounds(item.geometry.getBounds(), {checkZoomRange: 1, duration: 1000, zoomMargin: 20});
					},
					'Polygon'		: function (item) {
						item.options.set(ymaps.option.presetStorage.get('area#current'));
						item.balloon.open(item.geometry.getCoordinates()[0]);
						map.setBounds(item.geometry.getBounds(), {checkZoomRange: 1, duration: 1000, zoomMargin: 20});
					},
					'Circle'		: function (item) {
						item.options.set(ymaps.option.presetStorage.get('circle#current'));
						item.balloon.open(item.geometry.getCoordinates());
						map.setCenter(item.geometry.getCoordinates());
					},
					'Rectangle'		: function (item) {
						var cr = item.geometry.getCoordinates();
						item.options.set(ymaps.option.presetStorage.get('rct#current'));
						item.balloon.open([ (cr[0][0] + cr[1][0]) / 2, (cr[0][1] + cr[1][1]) / 2 ]);
						map.setBounds(item.geometry.getBounds(), {checkZoomRange: 1, duration: 1000, zoomMargin: 20});
					}
				};
			a_objects.each(function (item) {
				var cr;
				//console.log(item.properties.get('attr'));
				item.options.set(ymaps.option.presetStorage.get(item.properties.get('attr')));
				item.options.set('zIndex', 50);
				if (parseInt(item.properties.get('ttl'), 10) === id) {
					functions[item.geometry.getType()](item);
				}
			});
			paid_objects.each(function (item) {
				item.options.set(ymaps.option.presetStorage.get(item.properties.get('attr')));
				item.options.set('zIndex', 50);
				if (parseInt(item.properties.get('ttl'), 10) === id) {
					functions[item.geometry.getType()](item);
				}
			});
		}

		function perform_search(string) {
			$.ajax({
				url: mp.site + "foxhound/search",
				data: {
					sc: string,
					mapset: mp.mapset
				},
				type: "POST",
				dataType: "text",
				//crossDomain: true,
				success: function (data) {
					var a,
						arr;
					if (data.length) {
						arr = data.split(",");
						found = {};
						for (a in arr) {
							if(arr.hasOwnProperty(a)){
								found[parseInt(arr[a], 10)] = 1;
							}
						}
						a_objects.removeAll();
						paid_objects.removeAll();
						place_objectsWF(foreground, 'a', found);
						add_search();

						if(a_objects.getLength()) {
							//alert(a_objects.getLength());
							if (arr.length > 1) {
								map.setBounds(a_objects.getBounds(), {checkZoomRange: 1, duration: 1000, zoomMargin: 50});
							} else {
								a_objects.each(function (item) {
									map.setCenter(item.geometry.getCoordinates(), 13);
									return false;
								});
							}
						}
						/*
						if(paid_objects.getLength()) {
							//alert('paid');
							if (arr.length > 1) {
								map.setBounds(paid_objects.getBounds(), {checkZoomRange: 1, duration: 1000, zoomMargin: 50});
							} else {
								paid_objects.each(function (item) {
									map.setCenter(item.geometry.getCoordinates(), 13);
									return false;
								});
							}
						}
						*/
					}
				},
				error: function (data, stat, err) {
					console.log([data, stat, err].join("<br>"));
				}
			});
		}

		function mark_choices() {
			var d,
				string = {},
				z      = 0,
				f,
				tvalue;
			$('.itemcontainer img').attr('src', 'http://api.korzhevdp.com/images/clean_grey.png');
			for (d in switches) {
				if (switches.hasOwnProperty(d)) {
					switch (switches[d].fieldtype) {
					case "text":
						tvalue = ($('.itemcontainer[obj="' + d + '"] > input').val().length) ? $('.itemcontainer[obj="' + f + '"] > input').val() : 0;
						break;
					case "checkbox":
						tvalue = switches[d].value;
						if (tvalue === 1) {
							$('.itemcontainer[obj="' + d + '"] > img').attr('src', 'http://api.korzhevdp.com/images/clean.png');
						}
						break;
					case "select":
						tvalue = switches[d].value;
						break;
					}
					if (tvalue !== "0" && tvalue !== '' && tvalue !== 0) {
						string[d] = tvalue;
						z += 1;
					}
				}
			}
			if (z > 0) {
				perform_search(string);
			} else {
				unfilter_collections();
			}
		}

		function getLocation() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(showPosition);
			}
		}

		function showPosition(position) {
			var properties = { hasBalloon: 0, hasHint: 1, hintContent: "You are here", draggable: 0 },
				options    = ymaps.option.presetStorage.get("system#redflag");
			map.geoObjects.add(new ymaps.Placemark([position.coords.longitude, position.coords.latitude], properties, options));
			if(
				position.coords.longitude > (mp.center[0] - 1) || position.coords.longitude < (mp.center[0] + 1)
				&& position.coords.latitude > (mp.center[1] - 1) || position.coords.latitude < (mp.center[1] + 1)
			) {
				map.setCenter([position.coords.longitude, position.coords.latitude]);
			}
		}

		if ( mapset === 0 ) {
			if ( mp.otype !== 0 ) {
				$.ajax({
					url: mp.site + "map/msearch",
					data: {
						type: parseInt(mp.otype, 10)
					},
					type: "POST",
					//crossDomain: true,
					dataType: "script",
					success: function () {
						foreground = data;
						place_objects(foreground, 'a');
						add_search();
						if (a_objects.getLength() > 1) {
							map.setBounds(a_objects.getBounds(), {checkZoomRange: 1, duration: 1000, zoomMargin: 30});
						}
						$("#iFound").tab("show");
					},
					error: function (data, stat, err) {
						console.log([data, stat, err].join("<br>"));
					}
				});
			}
			if ( mp.otype === 0 ) {
				$.ajax({
					url: mp.site + "map/gsearch",
					data: {
						group: parseInt(mp.group, 10)
					},
					type: "POST",
					dataType: "script",
					//crossDomain: true,
					success: function () {
						foreground = data;
						place_objects(foreground, 'a');
						add_search();
						if (a_objects.getLength() > 1) {
							map.setBounds(a_objects.getBounds(), {checkZoomRange: 1, duration: 1000, zoomMargin: 50});
						}
						$("#iFound").tab("show");
					},
					error: function (data, stat, err) {
						console.log([data, stat, err].join("<br>"));
					}
				});
			}
		}

		if ( mapset !== 0 ) {
			$.ajax({
				url: mp.site + "map/get_map_content",
				data: { mapset: mapset },
				type: "POST",
				dataType: "script",
				//crossDomain: true,
				success: function () {
					a_objects.removeAll();
					showMode = 'direct';
					foreground = ac;
					background = bg;
					//place_objects(foreground, 'a');
					place_objects(background, 'b');
					$("#iSearch").tab("show");
					add_search();
				},
				error: function (data, stat, err) {
					console.log([data, stat, err].join("<br>"));
				}
			});
		}

		$('.itemcheckbox').unbind().click(function () {
			var obj = parseInt($(this).attr("obj"), 10);
			if (switches[obj].fieldtype === 'checkbox') {
				switches[obj].value = (switches[obj].value) ? 0 : 1;
			}
			mark_choices();
		});

		$('.itemtext').unbind().keyup(function () {
			var obj;
			if($(this).val().length > 1) {
				obj = parseInt($(this).attr('obj'), 10);
				switches[obj].value = $(this).val(); //<-------|
			} else {
				return false;
			}
			mark_choices();
		});

		$('.itemselect').unbind().change(function () {
			var obj = parseInt($(this).attr("obj"), 10);
			if($(this).val().length || $(this).val() != "0") {
				$('.itemselect[obj=' + obj + '] option').each(function() {
					var obj2 = $(this).val();
					if (switches[obj2] !== undefined) {
						switches[obj2].value = 0;
					}
				});
				obj = parseInt($(this).val(), 10);
				switches[obj].value = 1;
			} else {
				return false;
			}
			mark_choices();
		});
		getLocation();
	}

	$(".mapsw").click(function(){
		if($(this).attr("id") == "toGoogle") {
			map.setType("google#map");
			$("#toGoogle").addClass("active");
			$("#toYandex").removeClass("active");
		}
		if($(this).attr("id") == "toYandex") {
			map.setType("yandex#map");
			$("#toYandex").addClass("active");
			$("#toGoogle").removeClass("active");
		}
	});
	map.setType("google#map");
	$("#toGoogle").addClass("active");
	$("#toYandex").removeClass("active");
	styleAddToStorage(userstyles);
	load_mapset(mp.mapset);
}
ymaps.ready(init);
