<!-- Add Modal Begin -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open(base_url('diskon')); ?>
            <?= csrf_field(); ?>
            
            <div class="modal-body">
                <div class="mb-3">
                    <?= form_label('Tanggal', 'tanggal'); ?>
                    <?= form_input([
                        'type'        => 'date',
                        'name'        => 'tanggal',
                        'id'          => 'tanggal',
                        'class'       => 'form-control',
                        'required'    => true
                    ]); ?>
                </div>
            
                <div class="mb-3">
                    <?= form_label('Nominal', 'nominal'); ?>
                    <?= form_input([
                        'type'        => 'number',
                        'name'        => 'nominal',
                        'id'          => 'nominal',
                        'class'       => 'form-control',
                        'placeholder' => 'Nominal Diskon',
                        'required'    => true
                    ]); ?>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <?= form_submit('submit', 'Simpan', ['class' => 'btn btn-primary']); ?>
            </div>
            
            <?= form_close(); ?>
        </div>
    </div>
</div>
<!-- Add Modal End -->
