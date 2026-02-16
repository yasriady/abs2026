import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useState } from 'react'

export default function Index({ histories }) {
    const [editing, setEditing] = useState({ id: null, field: null })
    const [dirtyRowId, setDirtyRowId] = useState(null)

    const [form, setForm] = useState({
        begin_date: '',
        end_date: '',
    })

    const formatDate = (value) => {
        if (!value) return ''
        return value.slice(0, 10)
    }

    const startEdit = (row, field) => {
        setEditing({ id: row.id, field })

        if (dirtyRowId !== row.id) {
            setForm({
                begin_date: formatDate(row.begin_date),
                end_date: formatDate(row.end_date),
            })
        }
    }

    const onChangeDate = (field, value, row) => {
        setForm((prev) => ({
            ...prev,
            [field]: value,
        }))

        setDirtyRowId(row.id)
    }

    const save = (row) => {
        router.put(
            route('pegawai-historis-raw.update', row.id),
            {
                begin_date: form.begin_date,
                end_date: form.end_date,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setEditing({ id: null, field: null })
                    setDirtyRowId(null)
                },
            }
        )
    }

    const isDirty = (row) => dirtyRowId === row.id

    return (
        <AdminLayout title="Histori Pegawai (Raw)">
            <div className="box">
                <div className="box-body table-responsive">
                    <table className="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Unit</th>
                                <th style={{ width: 140 }}>Begin Date</th>
                                <th style={{ width: 140 }}>End Date</th>
                                <th style={{ width: 120 }}>Aksi</th>
                                <th style={{ width: 80 }}>Aktif</th>
                            </tr>
                        </thead>

                        <tbody>
                            {histories.data.map((row) => {
                                // 🔑 NORMALISASI FLAG
                                const isCurrent = Boolean(row.is_current)
                                const isOverlap = Boolean(row.is_overlap)

                                return (
                                    <tr
                                        key={row.id}
                                        style={{
                                            backgroundColor: isDirty(row)
                                                ? '#fdecea'
                                                : isOverlap
                                                    ? '#fff3cd'
                                                    : '',
                                        }}
                                    >
                                        <td>
                                            {row.master_pegawai?.nik}</td>

                                        <td
                                            style={{
                                                fontWeight: isCurrent ? 'bold' : 'normal',
                                            }}
                                        >
                                            {row.master_pegawai?.nama}
                                        </td>

                                        <td>{row.status_kepegawaian}</td>
                                        <td>{row.id_unit}</td>

                                        {/* BEGIN DATE */}
                                        <td
                                            style={{ cursor: 'pointer' }}
                                            onClick={() =>
                                                startEdit(row, 'begin_date')
                                            }
                                        >
                                            {editing.id === row.id &&
                                                editing.field === 'begin_date' ? (
                                                <input
                                                    type="date"
                                                    className="form-control input-sm"
                                                    value={form.begin_date}
                                                    autoFocus
                                                    onClick={(e) =>
                                                        e.stopPropagation()
                                                    }
                                                    onChange={(e) =>
                                                        onChangeDate(
                                                            'begin_date',
                                                            e.target.value,
                                                            row
                                                        )
                                                    }
                                                />
                                            ) : (
                                                formatDate(row.begin_date)
                                            )}
                                        </td>

                                        {/* END DATE */}
                                        <td
                                            style={{ cursor: 'pointer' }}
                                            onClick={() =>
                                                startEdit(row, 'end_date')
                                            }
                                        >
                                            {editing.id === row.id &&
                                                editing.field === 'end_date' ? (
                                                <input
                                                    type="date"
                                                    className="form-control input-sm"
                                                    value={form.end_date || ''}
                                                    onClick={(e) =>
                                                        e.stopPropagation()
                                                    }
                                                    onChange={(e) =>
                                                        onChangeDate(
                                                            'end_date',
                                                            e.target.value,
                                                            row
                                                        )
                                                    }
                                                />
                                            ) : (
                                                formatDate(row.end_date) || '-'
                                            )}
                                        </td>

                                        {/* AKSI */}
                                        <td className="text-center">
                                            {isDirty(row) && (
                                                <button
                                                    className="btn btn-xs btn-danger"
                                                    onClick={() => save(row)}
                                                >
                                                    <i className="fa fa-save" />{' '}
                                                    Save
                                                </button>
                                            )}

                                            {/* {isOverlap && (
                                                <span
                                                    className="label label-warning"
                                                    style={{
                                                        marginLeft: 6,
                                                    }}
                                                >
                                                    Overlap
                                                </span>
                                            )} */}

                                            {(isOverlap) && (
                                                <button
                                                    className="btn btn-xs btn-warning"
                                                    style={{ marginLeft: 6 }}
                                                    onClick={() => {
                                                        if (!confirm('Perbaiki seluruh histori pegawai ini secara otomatis?')) {
                                                            return
                                                        }

                                                        router.post(
                                                            route('pegawai-historis-raw.auto-fix', row.id),
                                                            {},
                                                            { preserveScroll: true }
                                                        )
                                                    }}
                                                >
                                                    <i className="fa fa-wrench" /> Auto Fix
                                                </button>
                                            )}

                                        </td>

                                        {/* AKTIF */}
                                        <td className="text-center">
                                            {isCurrent ? (
                                                <span className="label label-success">
                                                    Aktif
                                                </span>
                                            ) : (
                                                <span className="label label-default">
                                                    -
                                                </span>
                                            )}
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>

                    {/* FOOTER */}
                    <div className="box-footer clearfix">
                        <div
                            className="pull-left"
                            style={{ paddingTop: 6 }}
                        >
                            <small>
                                Menampilkan <b>{histories.from}</b> –{' '}
                                <b>{histories.to}</b> dari{' '}
                                <b>{histories.total}</b> data
                            </small>
                        </div>

                        <ul className="pagination pagination-sm no-margin pull-right">
                            {histories.links.map((link, i) => (
                                <li
                                    key={i}
                                    className={
                                        link.active
                                            ? 'active'
                                            : link.url
                                                ? ''
                                                : 'disabled'
                                    }
                                >
                                    {link.url ? (
                                        <a
                                            href="#"
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                            onClick={(e) => {
                                                e.preventDefault()
                                                router.visit(link.url, {
                                                    preserveScroll: true,
                                                })
                                            }}
                                        />
                                    ) : (
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
