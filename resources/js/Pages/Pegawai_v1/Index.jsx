import AdminLayout from '@/Layouts/AdminLayout'
import { router, usePage } from '@inertiajs/react'
import { useState, useEffect } from 'react'

export default function Index({ pegawais, filters, units, subUnits, stats }) {

    const { auth } = usePage().props
    const role = auth?.user?.role

    // const fotoUrl = (nik) =>
    //     nik
    //         ? `http://192.10.10.2/penduduk/foto/${nik}.jpg`
    //         : '/images/default-user.png'
    // const fotoUrl = (nik) =>
    //     nik ? `/pegawai/foto/${nik}` : '/images/default-user.png'
    const fotoUrl = (id) => id ? `/pegawai/foto/${id}` : '/images/default-user.png'


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
        router.delete(`/v1/pegawai/${id}`, { preserveState: true })
    }

    // ======================
    // AUTO LOAD SUB UNIT
    // ======================
    useEffect(() => {
        if (!unit) return

        router.get(
            '/v1/pegawai',
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

        router.get('/v1/pegawai', params, {
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
                            onClick={() => go('/v1/pegawai/create')}
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
                                <th width="60">Photo</th>
                                <th>Nama / NIP / NIK</th>
                                <th width="60">Status</th>
                                <th>Unit / Masa Kerja</th>
                                <th>Keterangan</th>
                                <th>Lokasi Kerja</th>
                                <th width="70">Order</th>
                                <th width="140">Options</th>
                            </tr>
                        </thead>

                        <tbody>
                            {pegawais.data.map((p, i) => {
                                const h = p.active_history

                                return (
                                    <tr key={p.id}>
                                        {/* NO */}
                                        <td>{(pegawais.meta?.from ?? 1) + i}</td>

                                        {/* PHOTO */}
                                        <td>
                                            <a
                                                href={fotoUrl(p.id)}
                                                data-lightbox="pegawai"
                                                data-title={`${p.nama} (${p.nik})`}
                                            >
                                                <img
                                                    src={fotoUrl(p.id)}
                                                    onError={(e) => {
                                                        e.target.onerror = null
                                                        e.target.src = '/images/default-user.png'
                                                    }}
                                                    style={{
                                                        width: 53,
                                                        height: 65,
                                                        objectFit: 'cover',
                                                        borderRadius: 2,
                                                        border: '1px solid #ddd',
                                                        cursor: 'zoom-in',
                                                    }}
                                                />
                                            </a>
                                        </td>


                                        {/* NAMA / NIP / NIK */}
                                        <td>
                                            <strong style={{ display: 'block' }}>{p.nama}</strong>
                                            <small>
                                                NIP: {p.nip || '-'}
                                                <br />
                                                NIK: {p.nik}
                                            </small>
                                        </td>

                                        {/* STATUS */}
                                        <td>
                                            {h ? (
                                                <span className="label label-info">
                                                    {h.status_kepegawaian}
                                                </span>
                                            ) : (
                                                <span className="label label-default">-</span>
                                            )}
                                        </td>

                                        {/* UNIT / MASA KERJA */}
                                        <td>
                                            {h ? (
                                                <>
                                                    <div>
                                                        <small>
                                                            {h.unit?.unit || h.id_unit}
                                                        </small>
                                                    </div>
                                                    <small>
                                                        {h.begin_date}
                                                        {h.end_date ? ` s/d ${h.end_date}` : ' – sekarang'}
                                                    </small>
                                                </>
                                            ) : (
                                                '-'
                                            )}
                                        </td>

                                        {/* KETERANGAN */}
                                        <td>-</td>

                                        {/* LOKASI */}
                                        <td>{h?.lokasi_kerja || '-'}</td>

                                        {/* ORDER */}
                                        <td>{h?.order ?? 0}</td>

                                        {/* OPTIONS */}
                                        <td>
                                            {/* EDIT PEGAWAI */}
                                            <button
                                                className="btn btn-xs btn-primary"
                                                title="Edit Pegawai"
                                                onClick={() => go(`/v1/pegawai/${p.id}/edit`)}
                                            >
                                                <i className="fa fa-pencil" />
                                            </button>

                                            {/* E-KTP (VIEW FOTO BESAR) */}
                                            {/* <button
                                                className="btn btn-xs btn-success"
                                                title="Lihat e-KTP"
                                                style={{ marginLeft: 4 }}
                                                onClick={() => window.open(fotoUrl(p.nik), '_blank')}
                                            >
                                                <i className="fa fa-id-card" />
                                            </button> */}

                                            {/* HISTORI */}
                                            {/* <button
                                                className="btn btn-xs btn-warning"
                                                title="Histori Pegawai"
                                                style={{ marginLeft: 4 }}
                                                onClick={() => go(`/v1/pegawai/${p.id}/histori`)}
                                            >
                                                <i className="fa fa-clock-o" />
                                            </button> */}

                                            {/* DELETE (OPSIONAL, JIKA ADA PERMISSION) */}
                                            {can('pegawai.delete') && (
                                                <button
                                                    className="btn btn-xs btn-danger"
                                                    title="Hapus Pegawai"
                                                    style={{ marginLeft: 4 }}
                                                    onClick={() => destroy(p.id)}
                                                >
                                                    <i className="fa fa-trash" />
                                                </button>
                                            )}
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
