import AdminLayout from '@/Layouts/AdminLayout'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal'
import { router, useForm } from '@inertiajs/react'
import { useMemo, useState } from 'react'

function toHm(value) {
  if (!value) return ''
  return String(value).slice(0, 5)
}

export default function Index({ jadwals, filters, hariOptions }) {
  const [modal, setModal] = useState(false)
  const [edit, setEdit] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [search, setSearch] = useState(filters.search || '')

  const hariMap = useMemo(() => {
    const map = {}
    ;(hariOptions || []).forEach((h) => {
      map[h.value] = h.label
    })
    return map
  }, [hariOptions])

  const { data, setData, post, put, reset, errors, processing } = useForm({
    hari: 1,
    start_date: '',
    end_date: '',
    jam_masuk: '',
    jam_pulang: '',
  })

  function openCreate() {
    setEdit(null)
    reset()
    setData({
      hari: 1,
      start_date: '',
      end_date: '',
      jam_masuk: '',
      jam_pulang: '',
    })
    setModal(true)
  }

  function openEdit(item) {
    setEdit(item)
    setData({
      hari: item.hari,
      start_date: item.start_date || '',
      end_date: item.end_date || '',
      jam_masuk: toHm(item.jam_masuk),
      jam_pulang: toHm(item.jam_pulang),
    })
    setModal(true)
  }

  function closeModal() {
    setModal(false)
    setEdit(null)
    reset()
  }

  function submit(e) {
    e.preventDefault()

    if (edit) {
      put(`/jam-kerja/dinas/${edit.id}`, {
        preserveScroll: true,
        onSuccess: closeModal,
      })
      return
    }

    post('/jam-kerja/dinas', {
      preserveScroll: true,
      onSuccess: closeModal,
    })
  }

  function doSearch(value) {
    setSearch(value)
    router.get(
      '/jam-kerja/dinas',
      { search: value },
      { preserveState: true, replace: true }
    )
  }

  return (
    <AdminLayout title="Jadwal Dinas">
      <div className="box">
        <div className="box-header">
          <button className="btn btn-primary" onClick={openCreate}>
            Tambah Jadwal Dinas
          </button>

          <div className="pull-right">
            <input
              className="form-control"
              placeholder="Search..."
              style={{ width: '220px' }}
              value={search}
              onChange={(e) => doSearch(e.target.value)}
            />
          </div>
        </div>

        <div className="box-body table-responsive">
          <table className="table table-bordered table-striped">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Hari</th>
                <th>Mulai Berlaku</th>
                <th>Akhir Berlaku</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {jadwals.data.map((item, i) => (
                <tr key={item.id}>
                  <td>{(jadwals.from || 1) + i}</td>
                  <td>{hariMap[item.hari] || item.hari}</td>
                  <td>{item.start_date || '-'}</td>
                  <td>{item.end_date || '-'}</td>
                  <td>{toHm(item.jam_masuk)}</td>
                  <td>{toHm(item.jam_pulang)}</td>
                  <td>
                    <button
                      className="btn btn-xs btn-warning"
                      onClick={() => openEdit(item)}
                    >
                      Edit
                    </button>
                    <button
                      className="btn btn-xs btn-danger"
                      onClick={() => setDeleteTarget(item)}
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              ))}

              {jadwals.data.length === 0 && (
                <tr>
                  <td colSpan="7" className="text-center text-muted">
                    Belum ada data jadwal dinas
                  </td>
                </tr>
              )}
            </tbody>
          </table>

          <div className="text-center">
            {jadwals.links.map((link, i) => (
              <button
                key={i}
                disabled={!link.url}
                className={`btn btn-xs ${link.active ? 'btn-primary' : 'btn-default'}`}
                onClick={() => router.get(link.url)}
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            ))}
          </div>
        </div>
      </div>

      {modal && (
        <>
          <div className="modal fade in" style={{ display: 'block' }}>
            <div className="modal-dialog">
              <form className="modal-content" onSubmit={submit}>
                <div className="modal-header">
                  <button type="button" className="close" onClick={closeModal}>
                    x
                  </button>
                  <h4>{edit ? 'Edit' : 'Tambah'} Jadwal Dinas</h4>
                </div>

                <div className="modal-body">
                  <div className="form-group">
                    <label>Hari</label>
                    <select
                      className="form-control"
                      value={data.hari}
                      onChange={(e) => setData('hari', Number(e.target.value))}
                    >
                      {hariOptions.map((h) => (
                        <option key={h.value} value={h.value}>
                          {h.label}
                        </option>
                      ))}
                    </select>
                    {errors.hari && <div className="text-danger">{errors.hari}</div>}
                  </div>

                  <div className="row">
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Mulai Berlaku</label>
                        <input
                          type="date"
                          className="form-control"
                          value={data.start_date}
                          onChange={(e) => setData('start_date', e.target.value)}
                        />
                        {errors.start_date && (
                          <div className="text-danger">{errors.start_date}</div>
                        )}
                      </div>
                    </div>
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Akhir Berlaku</label>
                        <input
                          type="date"
                          className="form-control"
                          value={data.end_date}
                          onChange={(e) => setData('end_date', e.target.value)}
                        />
                        {errors.end_date && (
                          <div className="text-danger">{errors.end_date}</div>
                        )}
                      </div>
                    </div>
                  </div>

                  <div className="row">
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Jam Masuk</label>
                        <input
                          type="time"
                          className="form-control"
                          value={data.jam_masuk}
                          onChange={(e) => setData('jam_masuk', e.target.value)}
                        />
                        {errors.jam_masuk && (
                          <div className="text-danger">{errors.jam_masuk}</div>
                        )}
                      </div>
                    </div>
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Jam Pulang</label>
                        <input
                          type="time"
                          className="form-control"
                          value={data.jam_pulang}
                          onChange={(e) => setData('jam_pulang', e.target.value)}
                        />
                        {errors.jam_pulang && (
                          <div className="text-danger">{errors.jam_pulang}</div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>

                <div className="modal-footer">
                  <button type="button" className="btn btn-default" onClick={closeModal}>
                    Batal
                  </button>
                  <button className="btn btn-primary" disabled={processing}>
                    {processing ? 'Menyimpan...' : 'Simpan'}
                  </button>
                </div>
              </form>
            </div>
          </div>
          <div className="modal-backdrop fade in"></div>
        </>
      )}

      <ConfirmDeleteModal
        show={!!deleteTarget}
        title="Konfirmasi Hapus Jadwal Dinas"
        message={
          <p>
            Yakin menghapus jadwal:
            <br />
            <strong>
              {deleteTarget ? hariMap[deleteTarget.hari] : ''}
            </strong>
            {' | '}
            <strong>{deleteTarget ? toHm(deleteTarget.jam_masuk) : ''}</strong>
            {' - '}
            <strong>{deleteTarget ? toHm(deleteTarget.jam_pulang) : ''}</strong>
          </p>
        }
        onCancel={() => setDeleteTarget(null)}
        onConfirm={() => {
          if (!deleteTarget) return
          router.delete(`/jam-kerja/dinas/${deleteTarget.id}`, {
            preserveScroll: true,
            onSuccess: () => setDeleteTarget(null),
          })
        }}
      />
    </AdminLayout>
  )
}
