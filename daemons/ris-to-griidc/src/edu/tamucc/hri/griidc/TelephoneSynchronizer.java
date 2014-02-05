package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Arrays;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.DbColumnInfo;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

public class TelephoneSynchronizer {

	private static final String TableName = "Telephone";
	private static final String KeyColName = "Telephone_Key";
	private static final String CountryNumColName = "Country_Number";
	private static final String TelephoneNumColName = "Telephone_Number";

	private static boolean DeBug = false;

	private String msg = null;
	private TelephoneStruct tempTelephoneStruct = null;
	public static TelephoneSynchronizer instance = null;
	
	private TelephoneSynchronizer() {

	}

	public static TelephoneSynchronizer getInstance() {
		if (TelephoneSynchronizer.instance == null) {
			TelephoneSynchronizer.instance = new TelephoneSynchronizer();
		}
		return TelephoneSynchronizer.instance;
	}

	public boolean updateTelephoneTable(int targetCountry, String targetTelNum)
			throws TelephoneNumberException, TableNotInDatabaseException {
		this.tempTelephoneStruct = new TelephoneStruct(targetCountry,
				targetTelNum);
		boolean status = false;
		try {
			TelephoneStruct telStruct = this.findTelephoneTableRecord();
			if (telStruct == null) {
				this.addTelephoneTableRecord();
			}
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return status;
	}

	private boolean isValid() throws TelephoneNumberException {
		if (!MiscUtils.doesCountryExist(this.tempTelephoneStruct
				.getCountryNumber())) {
			msg = "Telephone number referes to a non existant country code: "
					+ this.tempTelephoneStruct.getCountryNumber();
			throw new TelephoneNumberException(msg);
		}
		MiscUtils.isValidPhoneNumber(this.tempTelephoneStruct
				.getTelephoneNumber());
		return true;
	}

	private boolean addTelephoneTableRecord() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException,
			TelephoneNumberException {
		boolean status = this.isValid();
		DbColumnInfo info[] = new DbColumnInfo[2];
		int ndx = 0;
		info[ndx++] = new DbColumnInfo(CountryNumColName, RdbmsUtils.DbInteger,
				String.valueOf(this.tempTelephoneStruct.getCountryNumber()),
				null);
		info[ndx++] = new DbColumnInfo(TelephoneNumColName,
				RdbmsUtils.DbCharacter,
				this.tempTelephoneStruct.getTelephoneNumber(), null);
		String query = RdbmsUtils.formatInsertStatement(TableName, info);
		if (TelephoneSynchronizer.isDeBug())
			System.out.println("TelephoneSynchronizer.add() query: " + query);
		status = RdbmsUtils.getGriidcDbConnectionInstance()
				.executeQueryBoolean(query);
		return status;
	}

	private TelephoneStruct findTelephoneTableRecord() throws SQLException,
			FileNotFoundException, ClassNotFoundException,
			PropertyNotFoundException, TableNotInDatabaseException {
		ResultSet rs = RdbmsUtils.getGriidcDbConnectionInstance()
				.selectAllValuesFromTable(TableName);
		String targetPhoneNumber = this.tempTelephoneStruct
				.getTelephoneNumber();
		String tn = null;
		int cn = -1;
		int telephoneKey = -1;
		while (rs.next()) {
			telephoneKey = rs.getInt(KeyColName);
			tn = rs.getString(TelephoneNumColName);
			cn = rs.getInt(CountryNumColName);
			if (targetPhoneNumber.equals(tn.trim())
					&& cn == this.tempTelephoneStruct.getCountryNumber()) {
				TelephoneStruct ts = new TelephoneStruct(telephoneKey, cn, tn);
				return ts;
			}
		}
		return null;
	}

	public TelephoneStruct getTempTelephoneStruct() {
		return this.tempTelephoneStruct;
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	public TelephoneStruct createTelephoneStruct(int countryNumber,
			String telephoneNumber) {
		return new TelephoneStruct(countryNumber, telephoneNumber);
	}

	public class TelephoneStruct {
		private int key = -1;
		private int countryNumber = -1;
		private String telephoneNumber = null;
		private String extension = null;

		public TelephoneStruct(int countryNumber, String telephoneNumber) {
			this(-1, countryNumber, telephoneNumber);
		}

		/**
		 * @param key
		 * @param countryNumber
		 * @param telephoneNumber
		 */
		public TelephoneStruct(int key, int countryNumber,
				String telephoneNumber) {
			super();
			this.key = key;
			this.countryNumber = countryNumber;
			this.telephoneNumber = telephoneNumber.trim();
			this.separateExtension();
		}

		public int getKey() {
			return key;
		}

		public void setKey(int key) {
			this.key = key;
		}

		public int getCountryNumber() {
			return countryNumber;
		}

		public void setCountryNumber(int countryNumber) {
			this.countryNumber = countryNumber;
		}

		public String getTelephoneNumber() {
			return telephoneNumber;
		}

		public void setTelephoneNumber(String telephoneNumber) {
			this.telephoneNumber = telephoneNumber;
		}

		/**
		 * for phone number that has extension on the end The ' x' is used in
		 * RIS records
		 */
		private String[] ExtensionPatterns = { " x", " ex", " EX", " X" };

		public void separateExtension() {
			String[] px = MiscUtils.separateTelephoneNumberExtension(
					this.telephoneNumber.trim(), this.ExtensionPatterns);
			this.telephoneNumber = px[0];
			this.extension = px[1];
		}

		@Override
		public String toString() {
			return "TelephoneStruct [key=" + key + ", countryNumber="
					+ countryNumber + ", telephoneNumber=" + telephoneNumber
					+ ", extension=" + extension + ", ExtensionPatterns="
					+ Arrays.toString(ExtensionPatterns) + "]";
		}

	}

	public static void main(String[] args) {
		TelephoneSynchronizer ts = TelephoneSynchronizer.getInstance();
		int targetCountry = 42;
		String[] targetTelNum = { "(505) 690-5673 x123", "123-456-7890",
				"123-456-7890 ex123", "012-345-6789   EX 4567"

		};

		TelephoneSynchronizer.setDeBug(true);
		try {
			for (String telNum : targetTelNum) {
				TelephoneStruct tst = ts.createTelephoneStruct(targetCountry,
						telNum);
				MiscUtils.isValidPhoneNumber(tst.getTelephoneNumber());
				System.out.println("Number: " + telNum);
				System.out.println("\t" + tst);
			}
		} catch (TelephoneNumberException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
}
