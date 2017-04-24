FgFilter = {
    getCondition: function( titleAnd, titleOr, titleIs, titleIsNot, titleIsBetween, titleIsNotBetween, titleContains, titleNotContains, titleBeginswith, titleNotBeginswith, titleEndwith, titleNotEndwith ) {
        return {
                    defaults: [{
                    title: titleAnd,
                            id: 'and'
                    }, {
                    title: titleOr,
                            id: 'or'
                    }],
                    date: [{
                    title: titleIs,
                            id: 'is'
                    }, {
                    title: titleIsNot,
                            id: 'is not'
                    }, {
                    title: titleIsBetween,
                            id: 'is between',
                            multiple: 1
                    }, {
                    title: titleIsNotBetween,
                            id: 'is not between',
                            multiple: 1
                    }],
                    number: [{
                    title: titleIs,
                            id: 'is'
                    }, {
                    title: titleIsNot,
                            id: 'is not'
                    }, {
                    title: titleIsBetween,
                            id: 'is between',
                            multiple: 1
                    }, {
                    title: titleIsNotBetween,
                            id: 'is not between',
                            multiple: 1
                    }],
                    select: [{
                    title: titleIs,
                            id: 'is'
                    }, {
                    title: titleIsNot,
                            id: 'is not'
                    }],
                    text: [{
                    title: titleContains,
                            id: 'contains'
                    }, {
                    title: titleNotContains,
                            id: 'contains not'
                    },
                    {
                    title: titleBeginswith,
                            id: 'begins with'
                    },
                    {
                    title: titleNotBeginswith,
                            id: 'begins not with'
                    },
                    {
                    title: titleEndwith,
                            id: 'end with'
                    },
                    {
                    title: titleNotEndwith,
                            id: 'end not with'
                    }
                
            ]
                };
    },
}