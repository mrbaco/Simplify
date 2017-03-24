document.addEventListener('DOMContentLoaded', function () {
  if (window.matchMedia('(min-width: 998px)').matches && document.getElementById('news')) {
    var slides = document.querySelectorAll('#slides .slide');
    var slideInterval = setInterval(nextSlide, 4000);
    var currentSlide = 0;
    
    function nextSlide() {
      goToSlide(currentSlide + 1);
    }
    
    function goToSlide(n) {
      slides[currentSlide].className = 'slide';
      currentSlide = (n+slides.length)%slides.length;
      slides[currentSlide].className = 'slide showing';
    }
    
    document.getElementById('news').onclick = function(){
      nextSlide();
    };
  }
  
  var currentImage = 'thumb_0';
  
  var setImage = function (image_id) {
    document.getElementById('current').style.backgroundImage = image_id.style.backgroundImage;
    document.getElementById(currentImage).className = 'thumb';
    image_id.className = 'thumb active';
    currentImage = image_id.id;
  };
  
  if (document.getElementById('photos')) {
    document.getElementById('thumbs').onclick = function (e) {
      if (e.target.className == 'thumb' && e.target.style.backgroundImage !== '') {
        setImage(e.target);
      }
    };
    
    document.getElementById('current').onclick = function (e) {
      var arr = currentImage.split('_');
      var next_el = arr[0] + '_' + (parseInt(arr[1]) + 1);
      
      if (document.getElementById(next_el)) setImage(document.getElementById(next_el));
      else setImage(document.getElementById('thumb_0'));
    };
  }
});