import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useState } from 'react'
import { useEffect } from 'react'

export default function Index({ pegawais, units, subUnits, filters, stats }) {

    // const fotoUrl = (nik) => nik ? `/pegawai/foto/${nik}` : '/images/default-user.png'
    const fotoUrl = (id) => id ? `/pegawai/foto/${id}` : '/images/default-user.png'

    const [unit, setUnit] = useState(filters.unit_id || '')
    const [subUnit, setSubUnit] = useState(filters.sub_unit_id || '')
    const [date, setDate] = useState(filters.date || '')
    const [search, setSearch] = useState(filters.search || '')

    function submitFilter(e) {
        e.preventDefault()

        router.get(route('absensi-harian.index'), {
            unit_id: unit,
            sub_unit_id: subUnit,
            date: date,
            search: search,
        }, {
            preserveState: true,
            replace: true,
        })
    }

    useEffect(() => {
        if (!unit) return

        router.get(
            route('absensi-harian.index'),
            {
                ...filters,
                unit_id: unit,
                sub_unit_id: '', // reset sub unit
            },
            {
                preserveState: true,
                replace: true,
            }
        )
    }, [unit])

    return (
        <AdminLayout title="Absensi Harian">
            {/* ================= FILTER ================= */}
            <div className="box">
                <form className="box-body" onSubmit={submitFilter}>
                    <div className="row">
                        <div className="col-md-3">
                            <label>Unit</label>
                            <select
                                className="form-control"
                                value={unit}
                                onChange={e => {
                                    setUnit(e.target.value)
                                    setSubUnit('')
                                }}
                            >
                                <option value="">- Semua Unit -</option>
                                {units.map(u => (
                                    <option key={u.id} value={u.id}>
                                        {u.unit} ({u.id})
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Sub Unit</label>
                            <select
                                className="form-control"
                                value={subUnit}
                                disabled={!unit}
                                onChange={e => setSubUnit(e.target.value)}
                            >
                                <option value="">- Semua Sub Unit -</option>
                                {subUnits.map(su => (
                                    <option key={su.id} value={su.id}>
                                        {su.sub_unit}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-2">
                            <label>Tanggal</label>
                            <input
                                type="date"
                                className="form-control"
                                value={date}
                                onChange={e => setDate(e.target.value)}
                            />
                        </div>

                        <div className="col-md-2">
                            <label>&nbsp;</label>
                            <button className="btn btn-primary btn-block">
                                Show
                            </button>
                        </div>

                        <div className="col-md-2">
                            <label>&nbsp;</label>
                            <input
                                className="form-control"
                                placeholder="Cari NIK / NIP / Nama"
                                value={search}
                                onChange={e => setSearch(e.target.value)}
                            />
                        </div>
                    </div>
                </form>
            </div>

            {/* ================= TABLE ================= */}
            <div className="box">
                <div className="box-body table-responsive">
                    <table className="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style={{ width: 260 }}>IDENTITY</th>
                                <th style={{ width: 138 }}>REF</th>
                                <th style={{ width: 188 }}>MASUK</th>
                                <th style={{ width: 188 }}>PULANG</th>
                                <th>KET</th>
                            </tr>
                        </thead>

                        <tbody>
                            {pegawais.data.map(p => (
                                <tr key={p.id}>
                                    {/* ===== IDENTITY ===== */}
                                    <td>
                                        <strong>{p.nama}</strong><br />

                                        <small>
                                            <em>
                                                {(p.active_history?.unit?.unit || '-').length > 40
                                                    ? (p.active_history?.unit?.unit || '-').slice(0, 40) + '…'
                                                    : (p.active_history?.unit?.unit || '-')}
                                            </em>
                                        </small><br />
                                        <small>
                                            <em>
                                                {(p.active_history?.sub_unit?.sub_unit || '-').length > 40
                                                    ? (p.active_history?.sub_unit?.sub_unit || '-').slice(0, 40) + '…'
                                                    : (p.active_history?.sub_unit?.sub_unit || '-')}
                                            </em>
                                        </small><br />


                                        Nik: {p.nik}<br />
                                        Nip: {p.nip || '-'}<br />

                                        <small>
                                            {(p.active_history?.status_kepegawaian || '-')} (
                                            {p.active_history?.begin_date || '-'}
                                            {p.active_history?.end_date
                                                ? ` - ${p.active_history.end_date}`
                                                : ''}
                                            )
                                        </small>
                                    </td>

                                    {/* ===== REF ===== */}
                                    <td>
                                        {/* FOTO */}
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

                                        <div style={{ marginTop: 6, fontSize: 12 }}>
                                            {filters.date || new Date().toISOString().slice(0, 10)}
                                        </div>

                                        <div style={{ marginTop: 4 }}>
                                            <span className="label label-danger">ALPA</span>
                                        </div>
                                    </td>

                                    {/* ===== MASUK ===== */}
                                    <td>
                                        {/* FOTO */}
                                        <img
                                            src={p.foto_url}
                                            alt={p.nama}
                                            style={{
                                                width: 53,
                                                height: 65,
                                                objectFit: 'cover',
                                                borderRadius: 4,
                                                border: '1px solid #ddd',
                                            }}
                                        />

                                        <div style={{ marginTop: 6, fontSize: 12 }}>
                                            {filters.date || new Date().toISOString().slice(0, 10)}
                                        </div>

                                        <div style={{ marginTop: 4 }}>
                                            <span className="label label-danger">ALPA</span>
                                        </div>
                                    </td>

                                    {/* ===== PULANG ===== */}
                                    <td>
                                        {/* FOTO */}
                                        <img
                                            src={p.foto_url}
                                            alt={p.nama}
                                            style={{
                                                width: 53,
                                                height: 65,
                                                objectFit: 'cover',
                                                borderRadius: 4,
                                                border: '1px solid #ddd',
                                            }}
                                        />

                                        <div style={{ marginTop: 6, fontSize: 12 }}>
                                            {filters.date || new Date().toISOString().slice(0, 10)}
                                        </div>

                                        <div style={{ marginTop: 4 }}>
                                            <span className="label label-danger">ALPA</span>
                                        </div>
                                    </td>

                                    {/* ===== KET ===== */}
                                    <td></td>
                                </tr>
                            ))}

                            {pegawais.data.length === 0 && (
                                <tr>
                                    <td colSpan="5" className="text-center text-muted">
                                        Tidak ada data pegawai
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    {/* ================= PAGINATION ================= */}
                    <div
                        className="clearfix"
                        style={{
                            display: 'flex',
                            justifyContent: 'space-between',
                            alignItems: 'center',
                            marginTop: 10,
                        }}
                    >
                        {/* ===== JUMLAH DATA ===== */}
                        {stats && (
                            <span
                                className="label label-warning"
                                style={{ fontSize: 12, padding: '4px 8px' }}
                                title={`Menampilkan ${stats.from || 0}–${stats.to || 0} dari ${stats.total} data`}
                            >
                                {stats.from || 0}–{stats.to || 0} / {stats.total}
                            </span>
                        )}

                        {/* ===== PAGINATION ===== */}
                        <div>
                            {pegawais.links.map((link, i) => (
                                <button
                                    key={i}
                                    className={`btn btn-xs ${link.active ? 'btn-primary' : 'btn-default'}`}
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
