<form method="post" enctype="multipart/form-data" class="text-start mt-3">
  <label class="form-label small mb-1" for="slipFile"><i class="bi bi-paperclip"></i> แนบสลิปการโอนเงิน (JPG / PNG / WEBP ไม่เกิน 5 MB)</label>
  <input type="file" id="slipFile" name="slip" accept="image/jpeg,image/png,image/webp" class="form-control mb-2" required>
  <div id="slipPrev" class="text-center mb-2" style="display:none">
    <img id="slipImg" alt="ตัวอย่างสลิปที่เลือก" style="max-width:220px;max-height:280px;border-radius:8px;border:1px solid #ffd1dc">
    <div class="small text-muted mt-1" id="slipName"></div>
  </div>
  <small class="text-muted d-block mb-3"><i class="bi bi-shield-lock"></i> ระบบตรวจว่าเป็นรูปภาพจริง และลบข้อมูลแฝง (เช่น พิกัด GPS) ออกจากรูปอัตโนมัติ แนะนำให้ปิดบังเลขบัญชีที่ไม่เกี่ยวข้องก่อนอัปโหลด</small>
  <button class="btn btn-pink w-100"><i class="bi bi-upload"></i> แจ้งชำระเงิน (แนบสลิป)</button>
</form>
<script>
document.getElementById('slipFile').addEventListener('change', function () {
  const f = this.files[0], box = document.getElementById('slipPrev');
  if (!f) { box.style.display = 'none'; return; }
  if (f.size > 5 * 1024 * 1024) { alert('ไฟล์ใหญ่เกิน 5 MB กรุณาเลือกไฟล์ใหม่'); this.value = ''; box.style.display = 'none'; return; }
  if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { alert('รองรับเฉพาะรูปภาพ JPG, PNG, WEBP'); this.value = ''; box.style.display = 'none'; return; }
  document.getElementById('slipImg').src = URL.createObjectURL(f);
  document.getElementById('slipName').textContent = f.name;
  box.style.display = 'block';
});
</script>