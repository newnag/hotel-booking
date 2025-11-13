<section>
    <p class="text-muted mb-3">
        เมื่อบัญชีของคุณถูกลบ ข้อมูลและทรัพยากรทั้งหมดจะถูกลบอย่างถาวร ก่อนลบบัญชี โปรดดาวน์โหลดข้อมูลหรือข้อมูลที่คุณต้องการเก็บไว้
    </p>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        ลบบัญชี
    </button>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title">คุณแน่ใจหรือไม่ว่าต้องการลบบัญชีของคุณ?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            เมื่อบัญชีของคุณถูกลบ ข้อมูลและทรัพยากรทั้งหมดจะถูกลบอย่างถาวร โปรดป้อนรหัสผ่านของคุณเพื่อยืนยันว่าคุณต้องการลบบัญชีของคุณอย่างถาวร
                        </p>

                        <div class="mb-3">
                            <label for="delete_password" class="form-label">รหัสผ่าน</label>
                            <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                                   id="delete_password" name="password" placeholder="รหัสผ่าน">
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบบัญชี</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
