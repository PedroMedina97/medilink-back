# API-CERAOR3D
## api de sistema CERAOR
/*
USUARIOS EXISTENTES PARA PRUEBAS:

OWNER:
- Email: owner@correo.com
- Password: (usar el hash existente o resetear)
- ID: 6b799ca9-f240-40ee-ae74-10b0c17fc118

DOCTOR:
- Email: doctor@correo.com 
- Password: (usar el hash existente o resetear)
- ID: 2090a8a9-d5b4-4293-affe-b3d027f50e51

PACIENTES:
- Email: mmorales@correo.com (Maria Morales)
- Email: rlopez@correo.com (Rosa López)
- Email: ljimenez@correo.com (Luis Jiménez)
- Email: cliente@correo.com (test lastname test)

ENDPOINTS DE PRUEBA:
1. Login: POST /user/login
2. Historial médico: GET /user/medical-history/{user_id}
3. Resumen médico: GET /user/medical-summary/{user_id}
4. Mi historial: GET /user/my-medical-history
5. Mi resumen: GET /user/my-medical-summary

EJEMPLO DE IDs PARA PRUEBAS:
- Maria Morales: 0eccb38c-1470-4b32-a3ce-58cfd5915c27
- Rosa López: 7ea0b663-238d-4255-889a-c99ff0c259ac
- Luis Jiménez: d0ac5a37-e9c6-4430-839b-2d7ae6bd9d6e
- Test cliente: 31fb7350-f8a1-473d-9518-51431da62f9c

EJEMPLO DE PRUEBA:
1. Login como doctor: doctor@correo.com
2. Ver historial de Maria: GET /user/medical-history/0eccb38c-1470-4b32-a3ce-58cfd5915c27
3. Login como paciente: mmorales@correo.com  
4. Ver mi historial: GET /user/my-medical-history