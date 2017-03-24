document.addEventListener('DOMContentLoaded', function () {
  if (e('type')) {
    e('type').onchange = function () {
      var current = this.options.selectedIndex;
      
      toggle(e('type_' + previousFinanceBlock), false);
      toggle(e('type_' + current), true);
      
      previousFinanceBlock = current;
    };
  }
});