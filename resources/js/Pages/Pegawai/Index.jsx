import AdminLayout from '@/Layouts/AdminLayout'
import { router, usePage } from '@inertiajs/react'
import { useState, useEffect } from 'react'

export default function Index({ pegawais, filters, units, subUnits, stats }) {

    const { auth } = usePage().props
    const role = auth?.user?.role

    // ======================
    // STATE (NORMALIZED)
    // ======================
    const [unit, setUnit] = useState(filters.unit_id ?? '')
    const [subUnit, setSubUnit] = useState(filters.sub_unit_id ?? '')
    const [date, setDate] = useState(
        filters.date || new Date().toISOString().slice(0, 10)
    )
    const [allDate, setAllDate] = useState(
        filters.all_date === true || filters.all_date === 'true'
    )
    const [search, setSearch] = useState(filters.search || '')

    const can = (perm) => auth?.permissions?.includes(perm)

    // ======================
    // NAV HELPERS
    // ======================
    function go(url) {
        router.get(url, {}, { preserveState: true })
    }

    function destroy(id) {
        if (!confirm('Hapus pegawai ini?')) return
        router.delete(`/pegawai/${id}`, { preserveState: true })
    }

    // ======================
    // AUTO LOAD SUB UNIT
    // ======================
    useEffect(() => {
        if (!unit) return

        router.get(
            '/pegawai',
            {
                ...filters,
                unit_id: unit,
                sub_unit_id: '',
            },
            {
                preserveState: true,
                replace: true,
            }
        )
    }, [unit])

    // ======================
    // SUBMIT FILTER
    // ======================
    function submitFilter(e) {
        e.preventDefault()

        const params = {
            unit_id: unit,
            sub_unit_id: subUnit,
            all_date: allDate,
            search: search,
        }

        if (!allDate) {
            params.date = date
        }

        router.get('/pegawai', params, {
            preserveState: true,
            replace: true,
        })
    }

    return (
        <AdminLayout title="Pegawai">
            {/* ================= FILTER ================= */}
            <div className="box">
                <form className="box-body" onSubmit={submitFilter}>
                    <div className="row">
                        {/* UNIT */}
                        <div className="col-md-3">
                            <label>Unit</label>
                            <select
                                className="form-control"
                                value={unit}
                                disabled={role === 'admin_unit'}
                                onChange={(e) => {
                                    setUnit(e.target.value)
                                    setSubUnit('')
                                }}
                            >
                                <option value="">- Semua Unit -</option>
                                {(units || []).map((u) => (
                                    <option key={u.id} value={u.id}>
                                        {u.unit} ({u.id})
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* SUB UNIT */}
                        <div className="col-md-3">
                            <label>Sub Unit</label>
                            <select
                                className="form-control"
                                value={subUnit}
                                disabled={!unit}
                                onChange={(e) => setSubUnit(e.target.value)}
                            >
                                <option value="">- Semua Sub Unit -</option>
                                {(subUnits || []).map((su) => (
                                    <option key={su.id} value={su.id}>
                                        {su.sub_unit} ({su.id})
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* TANGGAL */}
                        <div className="col-md-2">
                            <label>Tanggal</label>
                            <input
                                type="date"
                                className="form-control"
                                value={date}
                                disabled={!!allDate}
                                onChange={(e) => setDate(e.target.value)}
                            />
                        </div>

                        {/* ALL DATE */}
                        <div className="col-md-2">
                            <label>&nbsp;</label>
                            <div className="checkbox">
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={!!allDate}
                                        onChange={(e) => setAllDate(e.target.checked)}
                                    />{' '}
                                    All date
                                </label>
                            </div>
                        </div>

                        {/* SUBMIT */}
                        <div className="col-md-2">
                            <label>&nbsp;</label>
                            <button className="btn btn-danger btn-block">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {/* ================= ACTION ================= */}
            <div className="box">
                <div className="box-header">
                    {can('pegawai.create') && (
                        <button
                            className="btn btn-primary"
                            onClick={() => go('/pegawai/create')}
                        >
                            Tambah Pegawai
                        </button>
                    )}

                    <div className="pull-right">
                        <input
                            className="form-control"
                            style={{ width: 250 }}
                            placeholder="Cari NIK / NIP / Nama"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && submitFilter(e)}
                        />
                    </div>
                </div>

                {/* ================= TABLE ================= */}
                <div className="box-body table-responsive">
                    <table className="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>p_id | h_id</th>
                                <th>NIK</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Unit</th>
                                <th>Sub Unit</th>
                                <th>Lokasi</th>
                                <th>Aktif</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {pegawais.data.map((p, i) => {
                                const h = p.active_history || null

                                return (
                                    <tr key={p.id}>
                                        <td>{(pegawais.meta?.from ?? 1) + i}</td>
                                        <td>{p.id} | {h?.id || '-'}</td>
                                        <td>{p.nik}</td>
                                        <td>{p.nip || '-'}</td>
                                        <td>{p.nama}</td>

                                        <td>
                                            {h ? (
                                                <span className="label label-info">
                                                    {(h.status_kepegawaian || '-').toUpperCase()}
                                                </span>
                                            ) : (
                                                <span className="label label-default">-</span>
                                            )}
                                        </td>

                                        <td>{h?.id_unit || '-'}</td>
                                        <td>{h?.id_sub_unit || '-'}</td>
                                        <td>{h?.lokasi_kerja || '-'}</td>

                                        <td>
                                            {h?.is_active ? (
                                                <span className="label label-success">Aktif</span>
                                            ) : (
                                                <span className="label label-default">-</span>
                                            )}
                                        </td>

                                        {/* ===== AKSI DROPDOWN ===== */}
                                        <td>
                                            <div className="btn-group">
                                                <button
                                                    type="button"
                                                    className="btn btn-xs btn-default dropdown-toggle"
                                                    data-toggle="dropdown"
                                                >
                                                    Aksi <span className="caret"></span>
                                                </button>

                                                <ul className="dropdown-menu dropdown-menu-right">
                                                    <li>
                                                        <a onClick={() => go(`/pegawai/${p.id}/edit-master`)}>
                                                            <i className="fa fa-id-card"></i> Edit Identitas
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a onClick={() => go(`/pegawai/${p.id}`)}>
                                                            <i className="fa fa-eye"></i> Lihat
                                                        </a>
                                                    </li>

                                                    {can('pegawai.update') && (
                                                        <li>
                                                            <a onClick={() => go(`/pegawai/${p.id}/edit`)}>
                                                                <i className="fa fa-pencil"></i> Edit
                                                            </a>
                                                        </li>
                                                    )}

                                                    <li>
                                                        <a onClick={() => go(`/pegawai/${p.id}/histori`)}>
                                                            <i className="fa fa-clock-o"></i> Histori
                                                        </a>
                                                    </li>

                                                    {can('pegawai.delete') && (
                                                        <>
                                                            <li className="divider"></li>
                                                            <li>
                                                                <a
                                                                    style={{ color: 'red' }}
                                                                    onClick={() => destroy(p.id)}
                                                                >
                                                                    <i className="fa fa-trash"></i> Hapus
                                                                </a>
                                                            </li>
                                                        </>
                                                    )}
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>

                    <div style={{ height: 20 }} />

                    {/* ================= PAGINATION ================= */}
                    <div
                        className="clearfix"
                        style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}
                    >
                        {/* BADGE JUMLAH */}
                        {stats && (
                            <span
                                className="label label-warning"
                                style={{
                                    fontSize: 12,
                                    padding: '4px 8px',
                                    borderRadius: 3,
                                }}
                                title={`Menampilkan ${stats.from || 0}–${stats.to || 0} dari ${stats.total} pegawai`}
                            >
                                {stats.from || 0}–{stats.to || 0} / {stats.total}
                            </span>
                        )}

                        {/* TOMBOL PAGINATION */}
                        <div>
                            {pegawais.links.map((link, i) => (
                                <button
                                    key={i}
                                    className={`btn btn-xs ${link.active ? 'btn-primary' : 'btn-default'
                                        }`}
                                    disabled={!link.url}
                                    onClick={() =>
                                        router.get(link.url, {}, { preserveState: true })
                                    }
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
