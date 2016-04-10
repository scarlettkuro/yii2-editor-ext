function getChildren(element) {
    var id = element.
    parent('span.list-group-item').
    find('a[data-toggle=collapse]').
    attr('href');
    return $(id);
}

function checkChildren($parent) {
    getChildren($parent).
    find('.myCheckbox').
    find('i').
    removeClass('glyphicon-unchecked').
    addClass('glyphicon-check');
}

function checkRoot($parent) {
    $parent.
    find('i').
    removeClass('glyphicon-unchecked').
    addClass('glyphicon-check');
    checkChildren($parent);
}

function unCheckChildren($parent) {
    getChildren($parent).
    find('.myCheckbox').
    find('i').
    removeClass('glyphicon-check').
    addClass('glyphicon-unchecked');
}

function unCheckRoot($parent) {
    $parent.
    find('i').
    removeClass('glyphicon-check').
    addClass('glyphicon-unchecked');
    unCheckChildren($parent);
}

function toggleRoot($root) {
    if ($root.find('i').hasClass('glyphicon-check')) {
        unCheckRoot($root);
    } else {
        checkRoot($root);
    }
}

function clearCheck() {
    $('#categoryTypesWidget').find('.glyphicon-check').
    removeClass('glyphicon-check').
    addClass('glyphicon-unchecked');
}

function markParentsWithCheckedChildren() {
    var $categoryTypesWidget = $('#categoryTypesWidget');
    $categoryTypesWidget.find('span').removeClass('haveCheckedChildren');
    $categoryTypesWidget.find('.glyphicon-check').each(function () {
        window.lastElementChecked = $(this);
        $(this).parents('span').parents('div.list-group.collapse').each(function () {
            var thisId = $(this).attr('id');
            var parentSpan = $('a[href=#' + thisId + ']').parent('span');
            parentSpan.addClass('haveCheckedChildren');
        });
    })
}

/**
 * Global function. Used in NodeController.php
 * @param ids
 */
window.checkIds = function (ids) {
    clearCheck();
    for (var i = 0; i < ids.length; i++) {
        $('a[categoryelementid="categoryElementWithId_' + ids[i] + '"]').
        find('i').
        removeClass('glyphicon-unchecked').
        addClass('glyphicon-check');
    }

    markParentsWithCheckedChildren();
};

window.getCheckedCategoryIds = function () {
    var checkedIds = [];
    var $categoryTypesWidget = $('#categoryTypesWidget');

    $categoryTypesWidget.find('.glyphicon-check').each(function () {
        checkedIds.push($(this).parent('a').attr('categoryelementid').split('_')[1]);
    });
    return checkedIds;
};

$('a.myCheckbox').click(function (e) {
    e.preventDefault();

    toggleRoot($(this));

    markParentsWithCheckedChildren();

    return false;
});


