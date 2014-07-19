var BASE_URL = 'http://ecdesign.co'
var NOP = function() {}

var MODULE_QUESTION_ROW =   '<tr question_id=%d>' +
                            '   <td class="question-body">%s</td>' +
                            '   <td class="icon-cell">' +
                            '       <a href="#edit_question" onclick="HuskyHuntQuestionModal.show(this)">' +
                            '           <img class="img24x24" src="%s/images/edit_button.png" alt="edit" />' +
                            '       </a>' +
                            '   </td>' +
                            '   <td class="icon-cell">' +
                            '       <a href="#delete_question" onclick="HuskyHuntModule.delete_question(this)">' +
                            '           <img class="img24x24" src="%s/images/prohibition_button.png" alt="delete" />' +
                            '       </a>' +
                            '   </td>' +
                            '</tr>';

var QUESTION_ANSWER_ROW =   '<tr answer_id=%d>' +
                            '   <td class="answer-body">%s</td>' +
                            '   <td class="icon-cell">' +
                            '       <input type="checkbox" name="correct[]" value="%d" %s>' +
                            '   </td>' +
                            '   <td class="icon-cell">' +
                            '       <a href="#edit_answer" onclick="HuskyHuntAnswerModal.show(this)">' +
                            '           <img class="img24x24" src="%s/images/edit_button.png" alt="edit" />' +
                            '       </a>' +
                            '   </td>' +
                            '   <td class="icon-cell">' +
                            '       <a href="#delete_answer" onclick="HuskyHuntQuestion.delete_answer(this)">' +
                            '           <img class="img24x24" src="%s/images/prohibition_button.png" alt="delete" />' +
                            '       </a>' +
                            '   </td>' +
                            '</tr>';

var TIMELINE_ROW =          '<tr timeline_id=%d>' +
                            '   <td>%s</td>' +
                            '   <td>%s</td>' +
                            '   <td class="icon-cell">' +
                            '       <a href="#remove_timeline" onclick="HuskyHuntModuleUI.remove_timeline(this);">' +
                            '           <img class="img24x24" src="%s/images/prohibition_button.png" alt="edit" />' +
                            '       </a>' +
                            '   </td>' +
                            '</tr>';




HuskyHuntModuleUI = {

    save: function(caller) {

        var module_id       = $('#module-form input[name=module_id]').val(); 
        var title           = $('#module-form input[name=module_title]').val(); 
        var body            = $('#module-form textarea[name=module_body]').val();
        var insight         = $('#module-form input[name=module_insight]').val(); 
        var points          = $('#module-form input[name=module_points]').val(); 
        var social_points   = $('#module-form input[name=module_social_points]').val(); 
        var postponable     = $('#module-form input[name=module_postponable]').prop('checked');
        var bonus           = $('#module-form input[name=module_bonus]').prop('checked');
        var knowledge_base           = $('#module-form input[name=module_knowledge_base]').prop('checked');

        // todo, add the module timeline

        module = {
            module_id: module_id,
            title: title,
            body: body,
            insight: insight,
            points: points,
            social_points: social_points,
            postponable: postponable,
            bonus: bonus,
            knowledge_base: knowledge_base,
            timeline: []
        }
        
        HuskyHuntModule.save(module, function(result) {
            if (result == true) {
                information('The module has been successfully saved.');
            } else {
                
                information('The module FAILED to save.');
            }
        });

    },

    add_timeline: function(caller) {

        var module_id   = $('#module-form input[name=module_id]').val();
        var start       = $('#datetimepicker-start input').val();
        var stop        = $('#datetimepicker-stop input').val();
       
        timeline = {
            module_id:  module_id,
            start:      start,
            stop:       stop
        };

        HuskyHuntModule.add_timeline(timeline, function(result) {

            if (result !== false) {
                timeline_id = parseInt(result, 10);
                var el = $(sprintf(TIMELINE_ROW, timeline_id, start, stop, BASE_URL));
                $('#timeline tbody').append(el); 
            }
        });

    },

    remove_timeline: function(caller) {
        
        var module_id   = $('#module-form input[name=module_id]').val();
        var timeline_id = $(caller).closest('tr').attr('timeline_id');

        timeline = {
            module_id: module_id,
            timeline_id: timeline_id
        };       
        
        HuskyHuntModule.remove_timeline(timeline, function(result) {
            
            if (result) {
                $(caller).closest('tr').remove();
            }

        });

 

    }

};


HuskyHuntModule = {
 
    save: function(module, callback) { 
    
        var SAVE_URL = BASE_URL + '/admin/ajax/save_module.php';

        module      = module    || {};
        callback    = callback  || NOP;

        var data = $.param(module);

        $.ajax({
            type:       'POST',
            url:        SAVE_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });

    },

   
    add_timeline: function(timeline, callback) {

        var ADD_TIMELINE_URL = BASE_URL + '/admin/ajax/add_timeline.php';

        timeline = timeline || {};
        callback = callback || NOP;
        
        data = $.param(timeline);

        $.ajax({
            type:       'POST',
            url:        ADD_TIMELINE_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });
    },

    remove_timeline: function(timeline, callback) {

        var REMOVE_TIMELINE_URL = BASE_URL + '/admin/ajax/remove_timeline.php';

        timeline = timeline || {};
        callback = callback || NOP;
        
        data = $.param(timeline);

        $.ajax({
            type:       'POST',
            url:        REMOVE_TIMELINE_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });
    },


    new_question: function(caller) {

        var module_id = $('#module-form input[name=module_id]').val();

        HuskyHuntModule.add_question(module_id, null, function(question) {
            
            question_id = parseInt(question.question_id, 10); 
            
            var el = $(sprintf(MODULE_QUESTION_ROW, question_id, question.body, BASE_URL, BASE_URL));
            $('#module-questions').append(el);

        });
    

    },

    delete_question: function(caller) {

        var module_id   = $('#module-form input[name=module_id]').val();
        var question_id = $(caller).closest('tr').attr('question_id');

        confirmation("Are you sure you want to delete this question?", function(result) {       
            
            if (result) { 
                HuskyHuntModule.remove_question(module_id, question_id, function(result) {

                    if (result === true) 
                        $(caller).closest('tr').remove();

                });
            }
        });


    },

    add_question: function(module_id, question_id, callback) { 
    
        var ADD_URL = BASE_URL + '/admin/ajax/add_question.php';

        module_id   = parseInt(module_id, 10) || null;
        question_id = parseInt(question_id, 10) || 'NEW';
        callback    = callback || NOP;

        if (module_id !== null) {

            var data = $.param({
                module_id: module_id,
                question_id: question_id
                });

            $.ajax({
                type:       'POST',
                url:        ADD_URL,
                dataType:   'json',
                data:       data,
                success:    callback
            });
        }


    },

    remove_question: function(module_id, question_id, callback) {

        var REMOVE_URL = BASE_URL + '/admin/ajax/remove_question.php';
       
        module_id   = parseInt(module_id, 10)   || null;
        question_id = parseInt(question_id, 10) || null;
        callback    = callback || NOP;

        if ((module_id !== null) && (question_id !== null)) {

            var data = $.param({
                module_id: module_id,
                question_id: question_id
                });
            
            $.ajax({
                type:       'POST',
                url:        REMOVE_URL,
                dataType:   'json',
                data:       data,
                success:    callback
            });
        }
    }

}

HuskyHuntQuestion = {

    load: function(question_id, callback) {
    
        var LOAD_URL = BASE_URL + '/admin/ajax/load_question.php';

        callback = callback || NOP;

        var data = $.param({question_id: question_id});

        $.ajax({
            type:       'POST',
            url:        LOAD_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });

    },
    
    save: function(question, callback) {
    
        var SAVE_URL = BASE_URL + '/admin/ajax/save_question.php';

        question    = question || {};
        callback    = callback || NOP;

        data = $.param(question);    
   
        $.ajax({
            type:       'POST',
            url:        SAVE_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });
    }, 
  
    new_answer: function(caller) {

        var question_id = $('#question-form input[name=question_id]').val();

        console.log(question_id);

        HuskyHuntQuestion.add_answer(question_id, null, function(answer) {
            
            answer_id = parseInt(answer.answer_id, 10); 
            
            var el  = $(sprintf(QUESTION_ANSWER_ROW, answer_id, answer.body, answer_id, '', BASE_URL, BASE_URL));  
    
            $('#ajax-question-answers').append(el);

        });
    

    },


    add_answer: function(question_id, answer_id, callback) {
        
        var ADD_URL = BASE_URL + '/admin/ajax/add_answer.php';

        question_id = parseInt(question_id, 10) || null;
        answer_id = parseInt(answer_id, 10) || null;

        if (question_id !== null) {

            data = $.param({
                question_id: question_id,
                answer_id: answer_id
            });

            $.ajax({
                type:       'POST',
                url:        ADD_URL,
                dataType:   'json',
                data:       data,
                success:    callback
            });
        }
    
    },

    remove_answer: function(question_id, answer_id, callback) {
         
        var REMOVE_URL = BASE_URL + '/admin/ajax/remove_answer.php';

        data = $.param({
            question_id: question_id,
            answer_id: answer_id
        });

        $.ajax({
            type:       'POST',
            url:        REMOVE_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });
    
    },

    delete_answer: function(caller) {

        var question_id = $('#question-form input[name=question_id]').val();
        var answer_id   = $(caller).closest('tr').attr('answer_id');

        HuskyHuntQuestion.remove_answer(question_id, answer_id, function(result) {
           
            if (result === true);
                $(caller).closest('tr').remove();

        });
    },

}


HuskyHuntAnswer = {

    load: function(answer_id, callback) {
      
        var LOAD_URL = BASE_URL + '/admin/ajax/load_answer.php';
        
        callback = callback || NOP;

        var data = $.param({answer_id: answer_id});

        $.ajax({
            type:       'POST',
            url:        LOAD_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });
    },

    save: function(answer, callback) {
 
        var SAVE_URL = BASE_URL + '/admin/ajax/save_answer.php';

        answer      = answer    || {};
        callback    = callback  || NOP;

        data = $.param(answer);    
   
        $.ajax({
            type:       'POST',
            url:        SAVE_URL,
            dataType:   'json',
            data:       data,
            success:    callback
        });

    }
}


HuskyHuntQuestionModal = {

    __collect_data: function() {

        var question_id     = $('#question-modal input[name=question_id]').val();
        var body            = $('#question-modal textarea[name=question_body]').val();

        var correct = new Array();

        $.each($('input[name="correct[]"]:checked'), function() {
            correct.push($(this).val());
        });

        var question = {
            question_id: question_id,
            body: body,
            correct: correct
        };

        return question;
    },

    save: function(caller) {

        var question = HuskyHuntQuestionModal.__collect_data();

        HuskyHuntQuestion.save(question, function() {
            var SELECTOR = '#module-questions tr[question_id=' + question.question_id + '] .question-body';
            $(SELECTOR).html(question.body);
            information('The question has been successfully saved.');
        });
    },
   
    save_hide: function(caller) {

        var question = HuskyHuntQuestionModal.__collect_data();
        
        HuskyHuntQuestion.save(question, function() {
            
            var SELECTOR = '#module-questions tr[question_id=' + question.question_id + '] .question-body';
            $(SELECTOR).html(question.body);
            //information('The question has been successfully saved.', function() {
                HuskyHuntQuestionModal.hide();
            //});
        });
    },

    load: function(question) {
 
        question = question || null; 
       
        $('#ajax-question-answers').html('');

        if (question !== null) {

            $('#ajax-question-id').val(question.question_id);
            CKEDITOR.instances['ajax-question-body'].setData(question.body);

            for (index in question.answers) {

                var answer  = question.answers[index];
                var answer_id = parseInt(answer.answer_id, 10); 
                //var correct = (question.correct.indexOf(answer.answer_id) > -1) ;
                var checked = (question.correct.indexOf(answer.answer_id) > -1) ? "checked" : "";
                var el      = $(sprintf(QUESTION_ANSWER_ROW, answer_id, answer.body, answer_id, checked, BASE_URL, BASE_URL));  

                $('#ajax-question-answers').append(el);
            }
        }

    },
     
    show: function(caller) {
     
        caller = caller || null;
        
        if (caller !== null) {
            question_id = $(caller).closest('tr').attr('question_id') || null;

            if (question_id != null) {
                HuskyHuntQuestion.load(question_id, HuskyHuntQuestionModal.load);
            }
        }

        polite_modal($('#question-modal'), 'show'); 

    },

    hide: function(caller) {
        
        polite_modal($('#question-modal'), 'hide'); 

    },

    remove_answer: function() {

    }
}



HuskyHuntAnswerModal = {

    __collect_data: function() {

        var answer_id   = $('#answer-modal input[name=answer_id]').val();
        var body        = $('#answer-modal textarea[name=answer_body]').val();

        var answer = {
            answer_id: answer_id,
            body: body
        };

        return answer;
    },

    save: function(caller) {

        var answer = HuskyHuntAnswerModal.__collect_data();

        HuskyHuntAnswer.save(answer, function(result) {
      
            var SELECTOR = '#ajax-question-answers tr[answer_id=' + answer.answer_id + '] .answer-body';
            $(SELECTOR).html(answer.body);
            information('The answer has been successfully saved.');
        });
    },
    
    save_hide: function(caller) {

        var answer = HuskyHuntAnswerModal.__collect_data();

        console.log(answer);

        HuskyHuntAnswer.save(answer, function(result) {

            console.log(result);

            var SELECTOR = '#ajax-question-answers tr[answer_id=' + answer.answer_id + '] .answer-body';
            $(SELECTOR).html(answer.body);
            //information('The answer has been successfully saved.', function() {
                HuskyHuntAnswerModal.hide();
            //});
        });
    },

    load: function(answer) {

        $('#answer-modal input[name=answer_id]').val(answer.answer_id);
        CKEDITOR.instances['answer-ckeditor'].setData(answer.body);

    },
    
    show: function(caller) {
     
        caller = caller || null;
        
        if (caller !== null) {
            answer_id = $(caller).closest('tr').attr('answer_id') || null;

            if (answer_id != null) {
                HuskyHuntAnswer.load(answer_id, HuskyHuntAnswerModal.load);
            }
        }

        polite_modal($('#answer-modal'), 'show'); 

    },

    hide: function(caller) {
        
        polite_modal($('#answer-modal'), 'hide'); 

    }
}

// if action show record the other open modals and save to object
// if action hide use recorded to restore modal state.
function polite_modal(target, action) {

    var modals = new Array();

    if (action == 'show') {

        $('.modal').each(function(index, el) {
        
            modal = $(el);
            if (modal.attr('aria-hidden') == 'false') {
                modals.push(modal);
                modal.modal('hide');
            }
        });

        target.data('polite_modal', modals);
        target.modal('show');
    } 

    if (action == 'hide') {

        $(target.data('polite_modal')).each(function(index, modal) {
            modal.modal('show');
        });
        target.modal('hide');

    }
}



$(document).ready(function() { 

//    $('#save_module').click(save_module);
//    $('#question-modal-save').click(HuskyHuntQuestion.save_modal); 
//    $('#answer-modal-save').click(HuskyHuntAnswerModal.save); 
    
    $('#datetimepicker-start').datetimepicker({language: 'en', pick12HourFormat: false});
    $('#datetimepicker-stop').datetimepicker({language: 'en'});

    $('#module-form input[name=module_insight]').keyup(function() {

        social = $(this).val();

        $('#module-form input[name=insight_character_count]').val(social.length);
    });

});


