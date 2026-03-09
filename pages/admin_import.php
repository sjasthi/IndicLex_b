<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm pref-card">
                <div class="card-body p-4">
                    <h2 class="section-title text-center mb-4" style="font-size: 2rem;">Bulk Import Dictionary</h2>
                    
                    <form action="import.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="dictionary_id" class="form-label fw-bold">Target Dictionary ID</label>
                            <input type="number" name="dictionary_id" id="dictionary_id" class="form-control" value="1" required>
                        </div>

                        <div class="mb-4">
                            <label for="dictionary_file" class="form-label fw-bold">Select Excel or CSV File</label>
                            <input type="file" name="dictionary_file" id="dictionary_file" class="form-control" accept=".csv, .xls, .xlsx" required>
                            <small class="text-muted">Format: Column A = Word, Column B = Translation. (Header row will be skipped).</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom w-100">Upload and Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>