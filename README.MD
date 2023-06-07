
# bitrix
## user
admin
## pass
admin12345

# db
## db-name
## user
c1_diploma_maks
## password
gsaS@cUY6
# shell-user
## user
chistomaks
## pass
h!rARxN7
```php
#include"iostream"
class A {
public:
    int m_x;
    int m_y;
    A() {

    }
    A(int x, int y) {
        m_x = x; m_y = y;
    }
};
class B {
public:
    A m_z;
    int m_k;
    B() { m_z = A(5, 10); m_k = 0;  }

};
int main() {
    B q;
    q.m_z.m_x = 7;

    std::cout << q.m_k;
}
```
//B() :, m_k(0) {}
