BX.ready(function () {
    const formComponentClass = {
        start: function () {
            formComponentClass.send();
            formComponentClass.selectAlgorithmMethod();
            formComponentClass.hideLicence();
        },
        selectAlgorithmMethod : function () {
            let oldMethod = BX('oldMethod');
            let newMethod = BX('newMethod');
            BX.bind(oldMethod, 'click', BX.proxy(function (event){
                newMethod.checked = false;
            }));
            BX.bind(newMethod, 'click', BX.proxy(function (event){
                oldMethod.checked = false;
            }));
        },
        send: function () {
            BX.bind(BX('form'), 'submit', BX.proxy(function (event) {
                let resultTextArea = BX('result');
                event.submitter.setAttribute('disabled', true);
                resultTextArea.value = '';
                BX.ajax.runComponentAction(
                    'diploma:form',
                    'check',
                    {
                        mode: 'class',
                        data: new FormData(event.srcElement),
                    }).then(response => {
                    if (response.status === 'success') {
                        resultTextArea.value = response.data.optimazeCode;
                        BX('class-count').value = response.data.optimazeData.classCount;
                        BX('class-copies').value = response.data.optimazeData.classOptimize;
                        BX('class-composition').value = response.data.optimazeData.classComposition;
                        BX('class-time').value = response.data.optimazeTime;
                    }
                }).catch(error => {
                    Object.values(error.errors).forEach(val => {
                        resultTextArea.value += val.message + '\n';
                    });
                });
                event.submitter.removeAttribute('disabled');
                return BX.PreventDefault(event);
            }));
        },
        hideLicence : function (){
            document.getElementsByClassName('tablebodytext')[0].remove()
        }

    }
    formComponentClass.start();
});
