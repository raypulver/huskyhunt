
<div class="modal fade" id="information-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h4 id="information-modal-message">Any information message?</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="information-modal-true" data-dismiss="modal">Okay</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmation-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h4 id="confirmation-modal-message">Any confirmation message?</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="confirmation-modal-false" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmation-modal-true">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

function confirmation(message, callback) {
    message = message || '';

    $('#confirmation-modal').modal({backdrop: false, keyboard: false});

    $('#confirmation-modal-message').html(message);
   
    $('#confirmation-modal-false').click(function(){
        $('#confirmation-modal').modal('hide');
        if (callback) callback(false);
    });

    $('#confirmation-modal-true').click(function(){
        $('#confirmation-modal').modal('hide');
        if (callback) callback(true);
    });
}

function information(message, callback) {
    message = message || '';

    $('#information-modal').modal({backdrop: false, keyboard: false});

    $('#information-modal-message').html(message);

    $('#information-modal-true').click(function(){
        $('#information-modal').modal('hide');
        if (callback) callback(true);
    });
}  

</script>


