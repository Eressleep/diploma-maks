BX.ready(function () {
  const formComponentClass = {
    start: function () {
      formComponentClass.send()
    },
    send: function () {
      BX.bind(BX('form'), 'submit', BX.proxy(function (event) {
        event.submitter.setAttribute('disabled', '')
        BX.ajax.runComponentAction(
          'diploma:form',
          'check',
          {
            mode: 'class',
            data: new FormData(event.srcElement)
          }).then(function (){

        }).catch()
        event.submitter.removeAttribute('disabled', '')
        return BX.PreventDefault(event);
      }))

    },
  }

  formComponentClass.start()
})
