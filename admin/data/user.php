<?php
$act = $_GET['act'] ?? '';

if ($act === 'tambah' || $act === 'ubah') {
    $row = ['id_user'=>'','nm_user'=>'','user_login'=>'1','level'=>'admin','status'=>'1'];
    if ($act === 'ubah') {
        $id = (int)($_GET['id_user'] ?? 0);
        $q = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id LIMIT 1");
        $row = mysqli_fetch_assoc($q) ?: $row;
    }
?>
<div class="page-head">
    <div>
        <h1><?php echo $act === 'tambah' ? 'Tambah User' : 'Edit User'; ?></h1>
        <p>Kelola akun admin yang dapat masuk ke dashboard.</p>
    </div>
    <a class="btn btn-secondary" href="index.php?menu=user"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="form-card">
    <form class="form-grid" action="dataUser.php?menu=user&act=<?php echo e($act); ?>" method="POST">
        <?php if ($act === 'ubah'): ?><input type="hidden" name="id_user" value="<?php echo e($row['id_user']); ?>"><?php endif; ?>
        <div class="form-group">
            <label>Nama User</label>
            <input class="form-control" type="text" name="nm_user" value="<?php echo e($row['nm_user']); ?>" required>
        </div>
        <div class="form-group">
            <label>Password <?php echo $act === 'ubah' ? 'Baru' : ''; ?></label>
            <input class="form-control" type="password" name="password" <?php echo $act === 'tambah' ? 'required' : ''; ?> placeholder="<?php echo $act === 'ubah' ? 'Kosongkan jika tidak diganti' : 'Masukkan password'; ?>">
        </div>
        <div class="form-group">
            <label>Level</label>
            <select class="form-control" name="level">
                <option value="admin" <?php echo ($row['level'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="gudang" <?php echo ($row['level'] === 'gudang') ? 'selected' : ''; ?>>Gudang</option>
                <option value="kasir" <?php echo ($row['level'] === 'kasir') ? 'selected' : ''; ?>>Kasir</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status Login</label>
            <select class="form-control" name="user_login">
                <option value="1" <?php echo ((int)$row['user_login'] === 1) ? 'selected' : ''; ?>>Aktif</option>
                <option value="0" <?php echo ((int)$row['user_login'] === 0) ? 'selected' : ''; ?>>Nonaktif</option>
            </select>
            <input type="hidden" name="status" value="1">
        </div>
        <div class="form-actions">
            <button type="submit"><?php echo $act === 'tambah' ? 'Simpan User' : 'Update User'; ?></button>
            <a class="btn btn-secondary" href="index.php?menu=user">Batal</a>
        </div>
    </form>
</div>
<?php
    return;
}

if ($act === 'hapus') {
    $id = (int)($_GET['id_user'] ?? 0);
    $q = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$id LIMIT 1");
    $row = mysqli_fetch_assoc($q);
?>
<div class="page-head">
    <div><h1>Hapus User</h1><p>Akun yang dihapus tidak bisa login lagi.</p></div>
    <a class="btn btn-secondary" href="index.php?menu=user"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="form-card">
    <?php if ($row): ?>
    <form class="form-grid" action="dataUser.php?menu=user&act=hapus" method="POST">
        <input type="hidden" name="id_user" value="<?php echo e($row['id_user']); ?>">
        <div class="form-group full"><label>User yang akan dihapus</label><div class="card card-pad"><b><?php echo e($row['nm_user']); ?></b><span class="cell-sub"><?php echo e($row['level']); ?></span></div></div>
        <div class="form-actions"><button type="submit" class="btn-danger">Hapus User</button><a class="btn btn-secondary" href="index.php?menu=user">Batal</a></div>
    </form>
    <?php else: ?><div class="empty-state">Data tidak ditemukan.</div><?php endif; ?>
</div>
<?php
    return;
}

$qUser = mysqli_query($conn, "SELECT * FROM user ORDER BY id_user DESC");
?>
<div class="page-head">
    <div><h1>Data User</h1><p>Daftar akun yang dapat mengelola dashboard Hanata Batik.</p></div>
    <a class="btn btn-primary" href="index.php?menu=user&act=tambah"><i class="bi bi-plus-circle"></i> Tambah User</a>
</div>
<div class="table-card">
    <div class="table-header"><div><h2>User Admin</h2><p>Login memakai kolom Nama User.</p></div></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>No</th><th>Nama User</th><th>Level</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if ($qUser && mysqli_num_rows($qUser) > 0): $no=1; ?>
                <?php while ($r = mysqli_fetch_assoc($qUser)): ?>
                <tr class="data-row">
                    <td><?php echo $no++; ?></td>
                    <td><b><?php echo e($r['nm_user']); ?></b></td>
                    <td><?php echo e($r['level']); ?></td>
                    <td><span class="badge-status <?php echo ((int)$r['status'] === 1) ? 'active' : 'off'; ?>"><?php echo ((int)$r['status'] === 1) ? 'Aktif' : 'Nonaktif'; ?></span></td>
                    <td><div class="actions"><a class="btn-edit" href="index.php?menu=user&act=ubah&id_user=<?php echo e($r['id_user']); ?>">Edit</a><a class="btn-delete" href="index.php?menu=user&act=hapus&id_user=<?php echo e($r['id_user']); ?>">Delete</a></div></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?><tr><td colspan="5" class="empty-state">Belum ada user.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
