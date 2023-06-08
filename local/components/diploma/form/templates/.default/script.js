BX.ready(function () {
    const formComponentClass = {
        start: function () {
            formComponentClass.send();
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
                        console.log(response.data.optimazeData)
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

    }
    formComponentClass.start();
});
