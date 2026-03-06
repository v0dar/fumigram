    window.addEventListener('DOMContentLoaded', event => {
    // Enable tooltips globally
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Enable popovers globally
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Activate Bootstrap scrollspy for the sticky nav component
    const navStick = document.body.querySelector('#navStick');
    if (navStick) {
        new bootstrap.ScrollSpy(document.body, {
            target: '#navStick',
            offset: 150,
        });
    }

    // Toggle the side navigation
    const drawerToggle = document.body.querySelector('#drawerToggle');
    if (drawerToggle) {
        drawerToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('drawer-toggled');
        });
    }

    // Close side navigation when width < LG
    const drawerContent = document.body.querySelector('#layoutDrawer_content');
    if (drawerContent) {
        drawerContent.addEventListener('click', event => {
            const BOOTSTRAP_LG_WIDTH = 992;
            if (window.innerWidth >= 992) {
                return;
            }
            if (document.body.classList.contains("drawer-toggled")) {
                document.body.classList.toggle("drawer-toggled");
            }
        });
    }


    // OverlayScrollbars(document.querySelectorAll('.knobs'), {})

    const selects = document.querySelectorAll('.select')
    const inputs = document.querySelectorAll('.input')
    const jsEditor = document.querySelector('#js-editor')
    const checkboxes = document.querySelectorAll('.checkbox input')
  
    const button = document.querySelector('#notify-btn')
  
    let notify_object = {
      status: 'success',
      title: 'Notify Title',
      text: 'Notify text lorem ipsum',
      effect: 'fade',
      speed: 300,
      customClass: '',
      customIcon: '',
      showIcon: true,
      showCloseButton: true,
      autoclose: false,
      autotimeout: 3000,
      gap: 20,
      distance: 20,
      type: 1,
      position: 'right top',
      customWrapper: ''
    }
  
    manipulateCode()
  
    selects.forEach((p) => {
      p.addEventListener('change', (e) => {
        const data = e.target.dataset.target
        const val = e.target.value
  
        switch (data) {
          case 'status':
            notify_object.status = val
            break
          case 'effect':
            notify_object.effect = val
            break
          case 'type':
            notify_object.type = parseInt(val)
            break
        }
        manipulateCode()
      })
    })
  
    inputs.forEach((p) => {
      p.addEventListener('input', (e) => {
        const data = e.target.dataset.target
        const val = e.target.value
  
        switch (data) {
          case 'title':
            notify_object.title = val
            break
          case 'text':
            notify_object.text = val
            break
          case 'customClass':
            notify_object.customClass = `${val}`
            break
          case 'customIcon':
            notify_object.customIcon = `${val}`
            break
          case 'speed':
            notify_object.speed = parseInt(val)
            break
          case 'autotimeout':
            notify_object.autotimeout = parseInt(val)
            break
          case 'gap':
            notify_object.gap = parseInt(val)
            break
          case 'distance':
            notify_object.distance = parseInt(val)
            break
          case 'position':
            notify_object.position = val
            break
          case 'custom-wrapper':
            notify_object.customWrapper = val
            break
        }
  
        manipulateCode()
      })
    })
  
    checkboxes.forEach((p) => {
      p.addEventListener('input', (e) => {
        const data = e.target.dataset.target
        const val = e.target.checked
  
        switch (data) {
          case 'autoclose':
            notify_object.autoclose = val
            break
          case 'showIcon':
            notify_object.showIcon = val
            break
          case 'showCloseButton':
            notify_object.showCloseButton = val
            break
        }
  
        manipulateCode()
      })
    })
  
    function manipulateCode() {
      jsEditor.innerHTML = Prism.highlight(
        `const btn = document.querySelector('#btn')
  
  btn.addEventListener('click', () => {
    new Notify ({
      status: '${notify_object.status}',
      title: '${notify_object.title}',
      text: '${notify_object.text}',
      effect: '${notify_object.effect}',
      speed: ${notify_object.speed},
      customClass: '${notify_object.customClass}',
      customIcon: '${notify_object.customIcon}',
      showIcon: ${notify_object.showIcon},
      showCloseButton: ${notify_object.showCloseButton},
      autoclose: ${notify_object.autoclose},
      autotimeout: ${notify_object.autotimeout},
      gap: ${notify_object.gap},
      distance: ${notify_object.distance},
      type: ${notify_object.type},
      position: '${notify_object.position}',
      customWrapper: '${notify_object.customWrapper}',
    })
  })`,
        Prism.languages.js,
        'js'
      )
    }
  
    button.addEventListener('click', () => {
      new Notify({
        ...notify_object
      })
    })

});
