var deleteEl = function (page, id) {
  if (!confirm('Удалить выбранный элемент?')) return false;
  window.location.href = '/cp/?m=catalog&g=main&p=' + page + '&remove=' + id;
};