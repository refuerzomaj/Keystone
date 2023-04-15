function real_estate_management_openNav() {
  jQuery(".sidenav").addClass('show');
}
function real_estate_management_closeNav() {
  jQuery(".sidenav").removeClass('show');
}

( function( window, document ) {
  function real_estate_management_keepFocusInMenu() {
    document.addEventListener( 'keydown', function( e ) {
      const real_estate_management_nav = document.querySelector( '.sidenav' );

      if ( ! real_estate_management_nav || ! real_estate_management_nav.classList.contains( 'show' ) ) {
        return;
      }

      const elements = [...real_estate_management_nav.querySelectorAll( 'input, a, button' )],
        real_estate_management_lastEl = elements[ elements.length - 1 ],
        real_estate_management_firstEl = elements[0],
        real_estate_management_activeEl = document.activeElement,
        tabKey = e.keyCode === 9,
        shiftKey = e.shiftKey;

      if ( ! shiftKey && tabKey && real_estate_management_lastEl === real_estate_management_activeEl ) {
        e.preventDefault();
        real_estate_management_firstEl.focus();
      }

      if ( shiftKey && tabKey && real_estate_management_firstEl === real_estate_management_activeEl ) {
        e.preventDefault();
        real_estate_management_lastEl.focus();
      }
    } );
  }
  real_estate_management_keepFocusInMenu();
} )( window, document );

var btn = jQuery('#button');

jQuery(window).scroll(function() {
  if (jQuery(window).scrollTop() > 300) {
    btn.addClass('show');
  } else {
    btn.removeClass('show');
  }
});

btn.on('click', function(e) {
  e.preventDefault();
  jQuery('html, body').animate({scrollTop:0}, '300');
});

jQuery(document).ready(function() {
  var owl = jQuery('#top-slider .owl-carousel');
    owl.owlCarousel({
      margin: 0,
      nav: true,
      autoplay:true,
      autoplayTimeout:3000,
      autoplayHoverPause:true,
      // autoHeight: true,
      loop: true,
      dots:false,
      navText : ['<i class="fa fa-lg fa-chevron-left" aria-hidden="true"></i>','<i class="fa fa-lg fa-chevron-right" aria-hidden="true"></i>'],
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 1
        },
        1024: {
          items: 1
      }
    }
  })
})

window.addEventListener('load', (event) => {
  jQuery(".loading").delay(2000).fadeOut("slow");
});

jQuery(window).scroll(function() {
  var data_sticky = jQuery('.socialmedia').attr('data-sticky');

  if (data_sticky == "true") {
    if (jQuery(this).scrollTop() > 1){
      jQuery('.socialmedia').addClass("stick_header");
    } else {
      jQuery('.socialmedia').removeClass("stick_header");
    }
  }
});
