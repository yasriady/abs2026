import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'

export default function Show({ pegawai }) {
    const h = pegawai.active_history

    return (
        <AdminLayout title="Detail Pegawai">
            <div className="box">
                <div className="box-body">
                    <table className="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="200">NIK</th>
                                <td>{pegawai.nik}</td>
                            </tr>
                            <tr>
                                <th>NIP</th>
                                <td>{pegawai.nip || '-'}</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>{pegawai.nama}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    {h ? (
                                        <span className="label label-info">
                                            {h.status_kepegawaian.toUpperCase()}
                                        </span>
                                    ) : (
                                        '-'
                                    )}
                                </td>
                            </tr>
                            <tr>
                                <th>Unit</th>
                                <td>{h?.id_unit || '-'}</td>
                            </tr>
                            <tr>
                                <th>Sub Unit</th>
                                <td>{h?.id_sub_unit || '-'}</td>
                            </tr>
                            <tr>
                                <th>Lokasi Kerja</th>
                                <td>{h?.lokasi_kerja || '-'}</td>
                            </tr>
                            <tr>
                                <th>Aktif</th>
                                <td>
                                    {h?.is_active ? (
                                        <span className="label label-success">
                                            Aktif
                                        </span>
                                    ) : (
                                        <span className="label label-default">
                                            Tidak Aktif
                                        </span>
                                    )}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button
                        className="btn btn-default"
                        onClick={() => router.get('/pegawai')}
                    >
                        Kembali
                    </button>
                </div>
            </div>
        </AdminLayout>
    )
}
