<script>

    <?php if(isset($logs)): ?>
    $('#datatable').dataTable({'order': [[0, 'desc']]});
    <?php endif; ?>
    <?php if(isset($changements)): ?>
    $('#datatable-eau').dataTable({'order': [[0, 'desc']]});
    <?php endif; ?>
    <?php if(isset($logs_mails)): ?>
    $('#datatable-mail').dataTable({'order': [[0, 'desc']]});
    <?php endif; ?>

</script>
