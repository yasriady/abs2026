import AdminLayout from '../../Layouts/AdminLayout'
import { useForm, router } from '@inertiajs/react'
import { useState } from 'react'

export default function Index({ mahasiswas, filters }) {
  const [modal, setModal] = useState(false)
  const [edit, setEdit] = useState(null)

  const { data, setData, post, put, reset, errors } = useForm({
    nim: '',
    nama: '',
    jurusan: '',
    angkatan: '',
  })

  function openCreate() {
    reset()
    setEdit(null)
    setModal(true)
  }

  function openEdit(mhs) {
    setEdit(mhs)
    setData(mhs)
    setModal(true)
  }

  function closeModal() {
    reset()
    setEdit(null)
    setModal(false)
  }

  function submit(e) {
    e.preventDefault()

    if (edit) {
      put(`/mahasiswa/${edit.id}`, {
        onSuccess: () => closeModal(),
      })
    } else {
      post('/mahasiswa', {
        onSuccess: () => closeModal(),
      })
    }
  }

  return (
    <AdminLayout title="Mahasiswa">

      <div className="box">
        <div className="box-header">
          <button className="btn btn-primary" onClick={openCreate}>
            Tambah Mahasiswa
          </button>

          <div className="pull-right">
            <input
              className="form-control"
              placeholder="Search..."
              style={{ width: '200px' }}
              defaultValue={filters.search}
              onChange={e =>
                router.get('/mahasiswa', { search: e.target.value }, {
                  preserveState: true,
                  replace: true,
                })
              }
            />
          </div>
        </div>

        <div className="box-body table-responsive">
          <table className="table table-bordered table-striped">
            <thead>
              <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Jurusan</th>
                <th>Angkatan</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {mahasiswas.data.map((m, i) => (
                <tr key={m.id}>
                  <td>{i + 1}</td>
                  <td>{m.nim}</td>
                  <td>{m.nama}</td>
                  <td>{m.jurusan}</td>
                  <td>{m.angkatan}</td>
                  <td>
                    <button
                      className="btn btn-xs btn-warning"
                      onClick={() => openEdit(m)}
                    >
                      Edit
                    </button>
                    <button
                      className="btn btn-xs btn-danger"
                      onClick={() => {
                        if (confirm('Yakin hapus mahasiswa ini?')) {
                          router.delete(`/mahasiswa/${m.id}`, {
                            preserveScroll: true,
                          })
                        }
                      }}
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          <div className="text-center">
            {mahasiswas.links.map((link, i) => (
              <button
                key={i}
                disabled={!link.url}
                className="btn btn-default btn-xs"
                onClick={() => router.get(link.url)}
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            ))}
          </div>
        </div>
      </div>

      {/* MODAL */}
      {modal && (
        <div className="modal fade in" style={{ display: 'block' }}>
          <div className="modal-dialog">
            <form className="modal-content" onSubmit={submit}>
              <div className="modal-header">
                <h4>{edit ? 'Edit' : 'Tambah'} Mahasiswa</h4>
              </div>

              <div className="modal-body">
                <input
                  className="form-control"
                  placeholder="NIM"
                  value={data.nim}
                  onChange={e => setData('nim', e.target.value)}
                />
                {errors.nim && <div className="text-danger">{errors.nim}</div>}

                <input
                  className="form-control"
                  placeholder="Nama"
                  value={data.nama}
                  onChange={e => setData('nama', e.target.value)}
                />
                <input
                  className="form-control"
                  placeholder="Jurusan"
                  value={data.jurusan}
                  onChange={e => setData('jurusan', e.target.value)}
                />
                <input
                  className="form-control"
                  placeholder="Angkatan"
                  value={data.angkatan}
                  onChange={e => setData('angkatan', e.target.value)}
                />
              </div>

              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-default"
                  onClick={closeModal}
                >
                  Batal
                </button>
                <button className="btn btn-primary">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AdminLayout>
  )
}
