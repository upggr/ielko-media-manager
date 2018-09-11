jQuery(document).ready(function($) {
  $('#upload-btn').click(function(e) {
    e.preventDefault();
    var image = wp.media({
        title: 'Upload Image',
        multiple: false
      }).open()
      .on('select', function(e) {
        var uploaded_image = image.state().get('selection').first();
        console.log(uploaded_image);
        var image_url = uploaded_image.toJSON().url;
        $('#image_url').val(image_url);
      });
  });

  $('#upload-btn1').click(function(e) {
    e.preventDefault();
    var image1 = wp.media({
        title: 'Upload Image',
        multiple: false
      }).open()
      .on('select', function(e) {
        var uploaded_image = image1.state().get('selection').first();
        console.log(uploaded_image);
        var image_url = uploaded_image.toJSON().url;
        $('#image_url1').val(image_url);
      });
  });

  $('#roku_app').on('click', function(e) {
    $.post("http://factory.upg.gr/index.php?client=roku", {
        fname: $('#roku_app').attr("fname"),
        url: $('#roku_app').attr("url"),
        title: $('#roku_app').attr("title"),
        subtitle: $('#roku_app').attr("subtitle"),
        version: $('#roku_app').attr("version"),
        mm_icon_focus_sd: $('#roku_app').attr("mm_icon_focus_sd"),
        mm_icon_side_sd: $('#roku_app').attr("mm_icon_side_sd"),
        mm_icon_focus_hd: $('#roku_app').attr("mm_icon_focus_hd"),
        mm_icon_side_hd: $('#roku_app').attr("mm_icon_side_hd"),
        splash_screen_hd: $('#roku_app').attr("splash_screen_hd"),
        splash_screen_sd: $('#roku_app').attr("splash_screen_sd"),
        splash_screen_fhd: $('#roku_app').attr("splash_screen_fhd"),
        overhang_sd: $('#roku_app').attr("overhang_sd"),
        overhang_hd: $('#roku_app').attr("overhang_hd"),
        store_fhd: $('#roku_app').attr("store_fhd"),
        store_hd: $('#roku_app').attr("store_hd"),
        store_sd: $('#roku_app').attr("store_sd")
      },
      function(data, status) {
        window.open('http://factory.upg.gr/out/' + $('#roku_app').attr("fname") + '.zip');
      });

  });


  $('#tvos_app').on('click', function(e) {
    $.post("http://factory.upg.gr/index.php?client=tvos", {
        fname: $('#roku_app').attr("fname"),
        url: $('#roku_app').attr("url"),
        title: $('#roku_app').attr("title"),
        subtitle: $('#roku_app').attr("subtitle"),
        version: $('#roku_app').attr("version"),
        mm_icon_focus_sd: $('#roku_app').attr("mm_icon_focus_sd"),
        mm_icon_side_sd: $('#roku_app').attr("mm_icon_side_sd"),
        mm_icon_focus_hd: $('#roku_app').attr("mm_icon_focus_hd"),
        mm_icon_side_hd: $('#roku_app').attr("mm_icon_side_hd"),
        splash_screen_hd: $('#roku_app').attr("splash_screen_hd"),
        splash_screen_sd: $('#roku_app').attr("splash_screen_sd"),
        splash_screen_fhd: $('#roku_app').attr("splash_screen_fhd"),
        overhang_sd: $('#roku_app').attr("overhang_sd"),
        overhang_hd: $('#roku_app').attr("overhang_hd"),
        store_fhd: $('#roku_app').attr("store_fhd"),
        store_hd: $('#roku_app').attr("store_hd"),
        store_sd: $('#roku_app').attr("store_sd")
      },
      function(data, status) {
        window.open('http://factory.upg.gr/out/' + $('#tvos_app').attr("fname") + '.zip');
      });

  });


  $('#upload-btn2').click(function(e) {
    e.preventDefault();
    var image2 = wp.media({
        title: 'Upload Image',
        multiple: false
      }).open()
      .on('select', function(e) {
        var uploaded_image = image2.state().get('selection').first();
        console.log(uploaded_image);
        var image_url = uploaded_image.toJSON().url;
        $('#image_url2').val(image_url);
      });
  });
  $('#upload-btn3').click(function(e) {
    e.preventDefault();
    var image3 = wp.media({
        title: 'Upload Image',
        multiple: false
      }).open()
      .on('select', function(e) {
        var uploaded_image = image3.state().get('selection').first();
        console.log(uploaded_image);
        var image_url = uploaded_image.toJSON().url;
        $('#image_url3').val(image_url);
      });
  });

  $('#upload-btn4').click(function(e) {
    e.preventDefault();
    var image4 = wp.media({
        title: 'Upload Image',
        multiple: false
      }).open()
      .on('select', function(e) {
        var uploaded_image = image4.state().get('selection').first();
        console.log(uploaded_image);
        var image_url = uploaded_image.toJSON().url;
        $('#image_url4').val(image_url);
      });
  });

  $('#upload-btn5').click(function(e) {
    e.preventDefault();
    var image5 = wp.media({
        title: 'Upload Image',
        multiple: false
      }).open()
      .on('select', function(e) {
        var uploaded_image = image5.state().get('selection').first();
        console.log(uploaded_image);
        var image_url = uploaded_image.toJSON().url;
        $('#image_url5').val(image_url);
      });
  });

}); //last line