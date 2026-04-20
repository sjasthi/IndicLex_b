/**
 * Public search: autocomplete via jQuery AJAX -> GET api/search.php
 */
(function ($) {
  'use strict';

  var DEBOUNCE_MS = 280;
  var MIN_LEN = 1;
  var MAX_SUGGEST = 12;

  function dictParam() {
    var v = $('#search-dictionary-select').val();
    return v === 'all' || v === '' ? 'all' : String(v);
  }

  function modeParam() {
    return String($('#search-mode-select').val() || 'substring');
  }

  function hideList($list, $input) {
    $list.empty().attr('hidden', true);
    $input.attr('aria-expanded', 'false');
  }

  function showList($list, $input) {
    $list.removeAttr('hidden');
    $input.attr('aria-expanded', 'true');
  }

  $(function () {
    var $input = $('#search-q-input');
    var $list = $('#search-autocomplete-list');
    var $form = $input.closest('form');

    if (!$input.length || !$list.length) {
      return;
    }

    var apiBase = $input.data('api-search');
    if (!apiBase) {
      return;
    }

    var timer = null;
    var xhr = null;

    function fetchSuggestions(q) {
      if (xhr) {
        xhr.abort();
        xhr = null;
      }

      var url =
        apiBase +
        '?q=' +
        encodeURIComponent(q) +
        '&dict=' +
        encodeURIComponent(dictParam()) +
        '&mode=' +
        encodeURIComponent(modeParam()) +
        '&limit=' +
        MAX_SUGGEST +
        '&offset=0';

      xhr = $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
      })
        .done(function (data) {
          xhr = null;
          if (!data || !data.ok || !Array.isArray(data.results)) {
            hideList($list, $input);
            return;
          }
          if (data.results.length === 0) {
            hideList($list, $input);
            return;
          }

          $list.empty();
          data.results.forEach(function (row, idx) {
            var word = row.word || '';
            var sub = row.telugu || row.hindi || row.transliteration || '';
            var line = sub ? word + ' — ' + sub : word;
            var $opt = $('<div/>', {
              class: 'search-autocomplete-item',
              role: 'option',
              id: 'search-ac-opt-' + idx,
              text: line,
            });
            $opt.on('mousedown', function (e) {
              e.preventDefault();
              $input.val(word);
              hideList($list, $input);
              $form.trigger('submit');
            });
            $list.append($opt);
          });
          showList($list, $input);
        })
        .fail(function (_jq, status) {
          xhr = null;
          if (status === 'abort') {
            return;
          }
          hideList($list, $input);
        });
    }

    function schedule() {
      var q = $.trim($input.val());
      if (timer) {
        clearTimeout(timer);
        timer = null;
      }
      if (q.length < MIN_LEN) {
        if (xhr) {
          xhr.abort();
          xhr = null;
        }
        hideList($list, $input);
        return;
      }
      timer = setTimeout(function () {
        timer = null;
        fetchSuggestions(q);
      }, DEBOUNCE_MS);
    }

    $input.on('input', schedule);
    $('#search-dictionary-select, #search-mode-select').on('change', function () {
      schedule();
    });

    $input.on('keydown', function (e) {
      if (e.key === 'Escape') {
        if (xhr) {
          xhr.abort();
          xhr = null;
        }
        hideList($list, $input);
      }
    });

    $(document).on('click', function (e) {
      if (!$(e.target).closest('.search-wrap-autocomplete').length) {
        hideList($list, $input);
      }
    });
  });
})(jQuery);
