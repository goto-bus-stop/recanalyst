function select (selector, base) {
  return [].slice.call((base || document).querySelectorAll(selector))
}

// https://github.com/nescalante/is-descendant
function isDescendant (parent, child) {
  var node = child.parentNode

  while (node !== null) {
    if (node === parent) {
      return true
    }

    node = node.parentNode
  }

  return false
}

function deselectTabs (tablist) {
  select('[role="tab"]', tablist).forEach(function (tabHref) {
    var tab = tabHref.parentNode
    var target = tabHref.getAttribute('href')
    if (tab.classList.contains('is-active')) {
      tab.classList.remove('is-active')
      target && select(target).forEach(function (panel) {
        panel.classList.remove('is-active')
      })
    }
  })
}

function selectTab (tabHref) {
  var tab = tabHref.parentNode
  var target = tabHref.getAttribute('href')
  tab.classList.add('is-active')
  if (target) {
    select(target).forEach(function (panel) {
      panel.classList.add('is-active')
    })

    // Update the hash, but don't scroll.
    var oldScroll = document.body.scrollTop
    window.location.hash = target
    document.body.scrollTop = oldScroll
  }
}

function setActiveTab(tablist, name) {
  var selectedTab = select('[role="tab"][aria-controls="' + CSS.escape(name) + ']')[0]
  if (isDescendant(tablist, selectedTab)) {
    deselectTabs(tablist)
    selectTab(selectedTab)
  }
}

function makeTablist (tablist) {
  if (window.location.hash) {
    var tabName = window.location.hash.slice(1)
    setActiveTab(tablist, tabName)
  }

  window.addEventListener('hashchange', function () {
    var tabName = window.location.hash.slice(1)
    setActiveTab(tablist, tabName)
  })

  tablist.addEventListener('click', function (event) {
    if (event.target.getAttribute('role') !== 'tab') {
      return
    }

    event.preventDefault()
    deselectTabs(tablist)
    selectTab(event.target)
  }, false)
}

function makeBody (body) {
  body.classList.remove('nojs')
  body.classList.add('js')
}

select('.tabs').forEach(makeTablist)
select('body').forEach(makeBody)
